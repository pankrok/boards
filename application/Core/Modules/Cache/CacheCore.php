<?php

declare(strict_types = 1);

namespace Application\Core\Modules\Cache;

class CacheCore
{
	protected $mainDir;
	protected $fileName;
	protected $dirName;
	protected $data;
	protected $expire;
	protected $extension;
	protected $active;
		
	public function __construct($c)
	{
		$this->cache 	= $c['active'];
		$this->mainDir	= $c['cache_dir'];
		$this->expire 	= $c['cache_time'];
		$this->dirName	= 'default';
		$this->extension = $c['cache_ext'];
		if(!file_exists(MAIN_DIR . $this->mainDir . 'cache[0].cache')) 
			file_put_contents(MAIN_DIR . $this->mainDir . 'cache[0].cache', []);
	} 
	
	protected function getDir()
	{
		if(!is_dir(MAIN_DIR . $this->mainDir  . md5($this->dirName)))
			mkdir(MAIN_DIR . $this->mainDir  . md5($this->dirName));
		
		return MAIN_DIR . $this->mainDir  . md5($this->dirName) . '/';
	}
	
	public function setName($dname)
	{
		$this->dirName = $dname;
	}
	
	public function getName()
	{
		return $this->dirName;
	}	
	
	public function store($fname, $data, $exp = 0)
	{
		if(!$this->cache) return null;
		if(!isset($exp))
			$exp = $this->expire;

		$content = [
			'data' => $data,
			'expire' => (time()+$exp)
		];
		$content = serialize($content);
		
		if(file_put_contents(
			self::getDir() . md5($fname) . $this->extension,		
			$content
		))
		{
			$list = unserialize(file_get_contents(MAIN_DIR . $this->mainDir . 'cache[0].cache'));
			if(is_array($list))
			{
				$list += [
					self::getDir() . md5($fname) => (time()+$exp)
				];
			}
			else
			{
				$list = [
					self::getDir() . md5($fname) => (time()+$exp)
				];
			}
			file_put_contents(MAIN_DIR . $this->mainDir . 'cache[0].cache', serialize($list));
			return true;
		}		
		return false;

	}
	
	public function receive($fname)
	{
		if(!$this->cache) return null;
		if(file_exists(self::getDir() . md5($fname) . $this->extension)) 
		{
			$content = file_get_contents(self::getDir() . md5($fname) . $this->extension);
			$content = unserialize($content);
			return $content['data'];
			
		}
		return null;
	}
	
	public function delete($fname)
	{
		if(!$this->cache) return null;
		if(file_exists(self::getDir() . md5($fname) . $this->extension)) 
		{
			return unlink(self::getDir() . md5($fname) . $this->extension);			
		}
		return false;
	}
	
	public function deleteExpired()
	{
		if(!$this->cache) return null;
		$list = unserialize(file_get_contents(MAIN_DIR . $this->mainDir . 'cache[0].cache'));
		if(is_array($list))
		{
			foreach($list as $k => $v)
			{
				if($v != 0 && $v < time())
				{	
					if(file_exists($k))
					{
						if(!unlink($k . $this->extension))
							throw new \Exception('cannot delete cache file'.$k);
						unset($list[$k]);
					}
					else
					{
						unset($list[$k]);
					}
				}
			}
				
			file_put_contents(MAIN_DIR . $this->mainDir . 'cache[0].cache', serialize($list));
			
			return true;
		}
		return false;
	}
	
	public function clearCache()
	{
		$files = self::getDirContents(MAIN_DIR . $this->mainDir);
		foreach($files as $v)
		{
			if(is_file($v))
				unlink($v);
			
		}
		foreach($files as $v)
		{
			if(is_dir($v))
				rmdir($v);
		}

	}
	
	public function clearTwigCache($skin)
	{
		if(!isset($skin)) return false;
		
		$files =  self::getDirContents(MAIN_DIR . '/skins/' . $skin . '/cache/twig');
		foreach($files as $v)
		{
			if(is_file($v))
				unlink($v);
			
		}
		foreach($files as $v)
		{
			if(is_dir($v))
				rmdir($v);
		}
		
	}
	
	public function cleanAllSkinsCache()
	{
		$dirs = scandir(MAIN_DIR . '/skins');
		$dirs = array_diff($dirs, ['.', '..']);

		foreach($dirs as $dir)
		{
			self::clearTwigCache($dir);
		}
		
	}
	
	private function getDirContents($dir, &$results = array()) 
	{
		$files = scandir($dir);

		foreach ($files as $key => $value) {
			$path = realpath($dir . DIRECTORY_SEPARATOR . $value);
			if (!is_dir($path)) {
				$results[] = $path;
			} else if ($value != "." && $value != "..") {
				self::getDirContents($path, $results);
				$results[] = $path;
			}
		}

		return $results;
	}
	
}
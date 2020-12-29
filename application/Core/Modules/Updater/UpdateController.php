<?php

declare(strict_types=1);

namespace Application\Core\Modules\Updater;
use Application\Core\Controller;

class UpdateController extends Controller
{
	
	public function index($request, $response)
	{
		if(file_exists(MAIN_DIR . '/environment/Config/lock'))
		{
			self::status();
		}
		else
		{
			self::checkUpdate();		
		}
		return $response;
		
	}
	
	private function checkUpdate()
	{
		$host = 'https://' . unserialize(file_get_contents(MAIN_DIR . '/environment/Config/updates.dat'))['host'] 
			. '/updates/last.txt';
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $host);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, false);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$result = curl_exec($ch);
		
		$v = explode('.', $result);
		$bv = explode('.', base64_decode($this->settings['core']['version']));

		if(intval($v[0]) != intval($bv[0]) || intval($v[1]) != intval($bv[1]) || intval($v[2]) != intval($bv[2]))
		{
			echo json_encode(['status' => 'update start']);
			self::startUpdate();
		}else{
			echo json_encode(['status' => 'Boards is updated']);
		}
	}
	
	private function status()
	{
		if(!is_dir(MAIN_DIR . '/environment/Update/tmp')) 
		{
			return false;
		}
		else
		{
			$status = file_get_contents(MAIN_DIR . '/environment/Update/tmp/status.json');
			$statusArr = json_decode($status, true);
			foreach($statusArr as $k => $v)
			{
				if($v['start'] == 0 && $v['updated'] == 0)
				{
					$statusArr[$k]['start'] = 1;
					file_put_contents(MAIN_DIR . '/environment/Update/tmp/status.json', json_encode($statusArr));
					
					self::$k();
					
					$statusArr[$k]['updated'] = 1;
					file_put_contents(MAIN_DIR . '/environment/Update/tmp/status.json', json_encode($statusArr));
					
					break;
				}
				elseif($v['start'] == 1 && $v['updated'] == 0)
				{
					echo json_encode(['status' => 'in progress', 'data' => $statusArr]) ;
					break;
				}
			}			
			
			if(end($statusArr)['updated'] == 1)
			{
				self::endUpdate();
			}
			echo json_encode(['status' => 'in progress', 'data' => $statusArr]);
		}
	}
	
	private function updateFiles()
	{
		$this->log->info('Update files');
		$files = parse_ini_file(MAIN_DIR . '/environment/Update/tmp/files/files.ini', true);
		
		foreach($files as $k => $v)
		{
			$backup = file_get_contents(MAIN_DIR . $v['path'] . $k . '.php');
			$md5 = md5($backup);
			if($v['md5'] == $md5)
			{
				$this->log->info('Update application file: ' . $v['path'] . $k . '.php');
				$h = file_get_contents(MAIN_DIR . '/environment/Update/tmp/files' . $v['path'] . $k . '.php');
				file_put_contents(MAIN_DIR . $v['path'] . $k . '.php', $h);
				file_put_contents(MAIN_DIR . $v['path'] . $k . '.php.back', $backup);
			}
			else
			{
				$this->log->error($v['path'].$k. ' md5 not match');	
				echo json_encode(['status' => 'Update Error!', 'message' => ['type' => 'alert-danger', 'data' => 'Update Error, check logs for details.']]);
				self::revert('files');
				self::deleteDir(MAIN_DIR.'/environment/Update/tmp');
				unlink(MAIN_DIR.'/environment/Config/lock');
				
				die();
			}
			
		}
		$this->log->info('Update files end');
	}
	
	
	
	private function updateDB()
	{
		$this->log->info('Update database');
	}
	
	private function updateSkins()
	{
		$this->log->info('Update skins');
	}
	
	private function startUpdate()
	{
		$this->log->info('Update start');
		$newfile = MAIN_DIR . '/environment/Config/lock';
		$fh = fopen($newfile, 'w') or die("Can't create update lock!");
		fclose($fh);
		self::getPackage();
	}
	
	private function endUpdate()
	{
		self::clean();
		
		$settings = $this->settings;
		$settings['core']['version'] = file_get_contents(MAIN_DIR.'/environment/Update/tmp/version.txt');
		
		$data = json_encode($settings, JSON_PRETTY_PRINT);
		file_put_contents(MAIN_DIR.'/environment/Config/settings.json', $data);
		
		self::deleteDir(MAIN_DIR.'/environment/Update/tmp');
		unlink(MAIN_DIR.'/environment/Config/lock');
		$this->log->info('Boards update success');
		echo json_encode(['status' => 'Boards is updated']);
	}
	
	private function revert(string $step)
	{
		
		if($step == 'files')
		{
			$this->log->info('revert files');
			$files = parse_ini_file(MAIN_DIR . '/environment/Update/tmp/files/files.ini', true);
			foreach($files as $k => $v)
			{
				
				if(file_exists(MAIN_DIR . $v['path'] . $k . '.php.back'))
				{
					$content = file_get_contents(MAIN_DIR . $v['path'] . $k . '.php.back');
					file_put_contents(MAIN_DIR . $v['path'] . $k . '.php');
					unlink(MAIN_DIR . $v['path'] . $k . '.php.back');
				}
			}
			
			
		}
		// revert db
		
		// revert skins
	}
	
	private function clean()
	{
		$this->log->info('clean files');
		$files = parse_ini_file(MAIN_DIR . '/environment/Update/tmp/files/files.ini', true);
		foreach($files as $k => $v)
		{
			$this->log->info('delete backup file: ' . $v['path'] . $k . '.php.back');
			unlink(MAIN_DIR . $v['path'] . $k . '.php.back');			
		}
	}
	
	private function getPackage()
	{
		$this->log->info('downloading update package');
		$output_filename = MAIN_DIR . '/environment/Update/' . base64_decode($this->settings['core']['version']) . '.tar.gz';
		
		$host = 'https://' . unserialize(file_get_contents(MAIN_DIR . '/environment/Config/updates.dat'))['host'] 
			. '/updates/' . base64_decode($this->settings['core']['version']) . '.tar.gz';
		
		if(!file_exists($output_filename))
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $host);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_AUTOREFERER, false);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$result = curl_exec($ch);
			curl_close($ch);
			$fp = fopen($output_filename, 'w');
			fwrite($fp, $result);
			fclose($fp);
		}
		if(file_exists($output_filename) && !file_exists(substr($output_filename, 0, -3)))
		{		
			$p = new \PharData($output_filename);
			$p->decompress(); 
		}	
		$output_filename = substr($output_filename, 0, -3);
		
		if(file_exists($output_filename) && !is_dir(MAIN_DIR . '/environment/Update/tmp'))	
		{
			$phar = new \PharData($output_filename);
			
			if(!is_dir(MAIN_DIR . '/environment/Update/tmp')) {
				mkdir(MAIN_DIR . '/environment/Update/tmp', 0777, true);
			}
			$phar->extractTo(MAIN_DIR . '/environment/Update/tmp');
			unlink($output_filename);
		}
	}
	
	private function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            self::deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}
}
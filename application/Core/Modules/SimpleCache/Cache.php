<?php

declare(strict_types=1);

namespace Application\Core\Modules\SimpleCache;

use Psr\SimpleCache\CacheInterface;

abstract class Cache implements CacheInterface
{
    protected $mainDir;
    protected $fileName;
    protected $path;
    protected $data;
    protected $ttl;
    protected $extension;
    protected $active;
        
    public function __construct($c)
    {
        $this->cache 	= intval($c['active']);
        $this->mainDir	= $c['cache_dir'];
        $this->ttl 	= intval($c['cache_time'] ?? 300);
        $this->path	= MAIN_DIR . $c['cache_dir'] . md5('defult');
        $this->extension = $c['cache_ext'];
    }
    
    public function __get($property)
    {
        if ($this->cache === 1) {
            return $this->$property;
        } else {
            return null;
        }
    }
    
    public function setPath(string $path) : void
    {
        $this->path	= MAIN_DIR . $this->mainDir  . md5($path);
    }
    
    public function getPath() : string
    {
        return $this->path;
    }
    
    public function clearTwigCache($skin)
    {
        if (!isset($skin)) {
            return false;
        }
        
        $files =  self::getDirContents(MAIN_DIR . '/skins/' . $skin . '/cache/twig');
        foreach ($files as $v) {
            if (is_file($v)) {
                unlink($v);
            }
        }
        foreach ($files as $v) {
            if (is_dir($v)) {
                rmdir($v);
            }
        }
    }
    
    protected function getDirContents($dir, &$results = [])
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } elseif ($value !== '.' && $value !== '..') {
                self::getDirContents($path, $results);
                $results[] = $path;
            }
        }

        return $results;
    }
    
    public function cleanAllSkinsCache()
    {
        $dirs = scandir(MAIN_DIR . '/skins');
        $dirs = array_diff($dirs, ['.', '..']);

        foreach ($dirs as $dir) {
            self::clearTwigCache($dir);
        }
    }
}

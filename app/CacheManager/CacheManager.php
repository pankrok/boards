<?php

namespace App\CacheManager;
use App\Controllers\InfologController;

class CacheManager{

	private $cache;
	
	public function __construct($config, $logger)
	{
		$logger->info('');
		$logger->info('Page cache info: ');
		if($config['type'] == 'file') $this->cache = new FileCache($config, $logger);
		if($config['type'] == 'memcached') $this->cache = new Memcached($config, $logger);
	}
	
	public function cache()
	{
		return $this->cache;
	}

}
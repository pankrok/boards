<?php
 
namespace App\CacheManager;

/**
 * Memcached class
 *
 * @author Yuri
 * @for BOARDS Forum
 **/
 
class Memcached{
	private $logger;
  /**
   *  Memcached server
   *
   * @var ip
   */
  private $_host = 'localhost';
  /**
   * The name of the default cache file
   *
   * @var string
   */
  private $_cachename = 'default';
  /**
   * Port of Memeceched server
   *
   * @var int
   */
  private $_port = 11211;

    /**
   * instance of memcached
   *
   * @var string
   */
   
   private $memcached; 
   
   /**
   * Default constructor
   *
   * @param string|array [optional] $config
   * @return void
   */
	  public function __construct($config = null, $logger) {
		$this->logger = $logger;		
		$this->logger->info('memecached init');
		if (true === isset($config)) {
		  if (is_array($config)) {
			$this->setHost($config['server']['host']);
			$this->setPort($config['server']['port']);
			$this->setCache($config['name']);
			$this->cache();
		  }
		}
	  }
	
	public function store($key, $data, $expiration = 90)
	{
		$this->logger->info("store: $key");
		$storeData = serialize($data);
		$this->memcached->set($this->_cachename.$key, $storeData, $expiration);

	}
	
	public function retrieve($key)
	{
		$data = $this->memcached->get($this->_cachename.$key);

		if($data == true)
		{
			$this->logger->info("retrieve: $key");
			return unserialize($data);
		}else{
			$this->logger->warn("retrieve: $key");
		}
	}
	
	public function retrieveAll()
	{
		$keys = $this->memcached->getAllKeys();
		$this->memcached->getDelayed($keys);
		$store = $this->memcached->fetchAll();
		if(is_array($store))
		{
			foreach($store as $k => $v){
				$data[$v['key']] = unserialize($v['value']);
			}
		}
		else
		{
			$data = false;
		}
		return $data;
	}
	
	public function isCached($key = '')
	{
		$data = $this->memcached->get($this->_cachename.$key);
		if($data == true){
			$this->logger->info("isCached: $key");
			return true;
		}
		else{
			$this->logger->warn("isCached: $key");
			return false;
		}

	}
	
	public function erase($key)
	{
		$data = $this->memcached->get($this->_cachename.$key);
		if($data == true)
		{
			$this->logger->info("erase: $key");
			return $this->memcached->delete($this->_cachename.$key);
		}else{
			$this->logger->warn("erase: $key");
		}
	}
		
	public function eraseAll()
	{
		$this->memcached->flush();
	}
	
	public function cacheStats()
	{
		return $this->memcached->getStats();
	}
	
	public function retrieveAllKeys()
	{
		return $this->memecached->getAllKeys();
	}
	
	public function getCache()
	{
		return $this->_cachename;
	}
	
	public function setCache($ext){
		$this->logger->info("setCache: $ext");
		$this->_cachename = $ext;
	}
	
	public function eraseExpired() 
	{
		return null;
	}
		
	private function setHost($ext){
		$this->_host = $ext;
	}
	private function setPort($ext){
		$this->_port = $ext;
	  }
	private function cache(){
	$this->memcached = new \Memcached();
		try
		{
			$this->memcached->addServer($this->_host, $this->_port);
			//$this->memcached->setCompressThreshold(20000, 0.2);
		}
		catch (Exception $e)
		{
			echo 'ERR! CODE: '.$e->getCode().', MESSAGE:	'.$e->getMessage();
			die();
		}
	
}
  
  
  
  
  
}
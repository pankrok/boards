<?php

namespace App\Controllers\Plugins;

 /**
 * Plugin loader controller
 * @package BOARDS Forum 
 */

class PluginLoaderController
{
    /**
	*  Plugin Loader create and store array of plugin list example array:
	*
	*	[ plugin_name => [ 
	*						active => bool, 
	*						install => bool
	*					]						
	*	]
	**/
   
	protected $pluginList = [];
	protected $cache;
	
	/**
    * Constructor load plugin list from cache or create new list in cache 
    * @return void
    **/
	
    public function __construct($cache)
    {
		$this->cache = $cache;
		$this->cache->setCache('Plugins');	
		
		if($this->cache->isCached('Plugins')) 
		{
			
			$this->pluginList = ($this->cache->retrieve('Plugins'));
		
		}
		else
		{

			$allFiles = scandir(MAIN_DIR.'/plugins/'); 
			$files = array_diff($allFiles, ['.', '..']);
			
			foreach ($files as $k => $v)
			{               
				if(substr($v, -4 == '.php')){
					$name = 'Plugins\\'. substr($v, 0, -4);
					$list = [$name => ['active'	=> 1,
									'install' 	=> 1]
							]; 
				}
				
			}  	  
			
			#$this->cache->store('Plugins', $list, 3);
			$this->pluginList = $list;
			
		}
    }
	
	/**
    * @return array of plugins
    **/
	
    public function getPluginsList()
    {
       return $this->pluginList; 
    }

	/**
	* Reload plugin list
    * @return void
    **/
	public function reloadPluginsList()
	{
		if($this->cache->isCached('Plugins')) $this->cache->erase('Plugins');
		$allFiles = scandir(MAIN_DIR.'/plugins/'); 
		$files = array_diff($allFiles, ['.', '..']);

		if(is_array($files)){
			foreach ($files as $k => $v)
			{               
				if(substr($v, -4) == '.php'){
					$name = 'Plugins\\'. substr($v, 0, -4);
					$list = [$name => ['active'	=> 1,
									'install' 	=> 1]
							];
				}
			}
		}
		
		$this->cache->store('Plugins', $list, 3);
		$this->pluginList = $list;
		
	}
	
	public function activePlugin($PluginName)
	{
		
		
		
	}
	
	public function installPlugin($PluginName)
	{
		
		
		
	}
	
    
    
}

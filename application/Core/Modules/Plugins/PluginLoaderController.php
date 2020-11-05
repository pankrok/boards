<?php

namespace Application\Core\Modules\Plugins;

use Application\Models\PluginsModel;

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
   
	protected $cache;
	
	/**
    * Constructor load plugin list from cache or create new list in cache 
    * @return void
    **/
	
    public function __construct($cache)
    {
		$this->cache = $cache;
		
		
		if(!$cacheData = $cache->receive('Plugins'))
		{

			$allFiles = scandir(MAIN_DIR.'/plugins/'); 
			$files = array_diff($allFiles, ['.', '..']);
			
			foreach ($files as $k => $v)
			{               
				if(substr($v, -4 == '.php')){
					
					$name = 'Plugins\\'. substr($v, 0, -4);
					$plugin = PluginsModel::firstOrCreate(['plugin_name' => $name]);
					$plugin->active = true;
					$plugin->install = true;
					$plugin->save();
					
					$plugin = null;					
				}
				
			}  	  

			
		}
    }
	
	/**
    * @return array of plugins
    **/
	
    public function getPluginsList()
    {
       return PluginsModel::get()->toArray();
    }

	/**
	* Reload plugin list
    * @return void
    **/
	public function reloadPluginsList()
	{
		if($this->cache->receive('Plugins')) $this->cache->erase('Plugins');
		PluginsModel::truncate();
		
		$allFiles = scandir(MAIN_DIR.'/plugins/'); 
		$files = array_diff($allFiles, ['.', '..']);

		if(is_array($files)){
			foreach ($files as $k => $v)
			{               
				if(substr($v, -4 == '.php')){
					
					$name = substr($v, 0, -4);
					$plugin = PluginsModel::firstOrCreate(['plugin_name' => $name]); 
					$plugin->active = true;
					$plugin->install = true;
					$plugin->save();
					
					$plugin = null;					
				}
			}
		}
		
	}
	
	public function activePlugin($pluginName)
	{
		
		$plugin = PluginsModel::where('plugin_name', $pluginName);
		$plugin->active = true;
		
	}
	
	public function installPlugin($pluginName)
	{
		
		$plugin = PluginsModel::where('plugin_name', $pluginName);
		$plugin->install = true;
		
	}
	
    
}

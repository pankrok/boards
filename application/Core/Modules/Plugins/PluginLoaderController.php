<?php
declare(strict_types=1);
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
				if(substr($v, -4) == '.php'){
					
					$name = substr($v, 0, -4);
					$plugin = PluginsModel::firstOrCreate(['plugin_name' => $name]);
				
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
				if(substr($v, -4) == '.php'){
					
					$name = substr($v, 0, -4);
					$plugin = PluginsModel::firstOrCreate(['plugin_name' => $name]); 
					$plugin->save();
					
					$plugin = null;					
				}
			}
		}
		
	}
	
	public function activePlugin(string $pluginName)
	{
		
		$plugin = PluginsModel::where('plugin_name', $pluginName)->first();
		if($plugin->active) 
			return false;
		
		$pluginName = "\\Plugins\\$pluginName\\$pluginName" ;
		$pluginName::activation();		
		$plugin->active = true;		
		$plugin->save();
		return true;
		
	}
	
	public function deactivePlugin(string $pluginName)
	{
		
		$plugin = PluginsModel::where('plugin_name', $pluginName)->first();
		if(!$plugin->active) 
			return false;
		$pluginName = "\\Plugins\\$pluginName\\$pluginName" ;
		$pluginName::deactivation();		
		$plugin->active = false;
		$plugin->save();
		return true;
		
	}
	
	public function installPlugin($pluginName)
	{
		
		$plugin = PluginsModel::where('plugin_name', $pluginName)->first();
		if($plugin->install) 
			return false;
		$pluginName = "\\Plugins\\$pluginName\\$pluginName" ;
		$pluginName::install();		
		$plugin->install = true;
		$plugin->save();

	}
	
	public function uninstallPlugin($pluginName)
	{
		
		$plugin = PluginsModel::where('plugin_name', $pluginName)->first();
		if(!$plugin->install) 
			return false;
		$pluginName = "\\Plugins\\$pluginName\\$pluginName" ;
		$pluginName::uninstall();		
		$plugin->install = false;
		$plugin->save();
		
	}
    
}

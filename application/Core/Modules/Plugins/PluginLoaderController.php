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
    protected $skin;
    private $version;
    
    /**
    * Constructor load plugin list from cache or create new list in cache
    * @return void
    **/
    
    public function __construct($cache, $skin, $version)
    {
        $this->cache = $cache;
        $this->skin = $skin;
        $this->version = $version;
        
        
        if (!$cacheData = $cache->get('Plugins')) {
            $allFiles = scandir(MAIN_DIR.'/plugins/');
            $files = array_diff($allFiles, ['.', '..']);
            
            $plugins = PluginsModel::get()->toArray();
            foreach ($plugins as $val) {
                if (!array_search($val['plugin_name'], $files, true)) {
                    PluginsModel::find($val['id'])->delete();
                }
            }
            
            foreach ($files as $k => $v) {
                if (substr($v, -4) != '.php') {
                    $name = $v;
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
        if ($this->cache->get('Plugins')) {
            $this->cache->delete('Plugins');
        }
        
    
        $allFiles = scandir(MAIN_DIR.'/plugins/');
        $files = array_diff($allFiles, ['.', '..']);
        
        $plugins = PluginsModel::get()->toArray();
        foreach ($plugins as $val) {
            if (!array_search($val['plugin_name'], $files, true)) {
                PluginsModel::find($val['id'])->delete();
            }
        }
    
        if (is_array($files)) {
            foreach ($files as $k => $v) {
                if (substr($v, -4) != '.php') {
                    $name = $v;
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
        if ($plugin->active) {
            return false;
        }
        
        $pluginName = "\\Plugins\\$pluginName\\$pluginName";
        $handler = $pluginName::activation();
        if ($handler === false) {
            die($handler);
            return false;
        }
        $plugin->active = true;
        $plugin->save();
        
        $this->cache->clearTwigCache($this->skin);
        $this->cache->clear();
        return true;
    }
    
    public function deactivePlugin(string $pluginName)
    {
        $plugin = PluginsModel::where('plugin_name', $pluginName)->first();
        if (!$plugin->active) {
            return false;
        }
        $pluginName = "\\Plugins\\$pluginName\\$pluginName" ;
        $pluginName::deactivation();
        $plugin->active = false;
        $plugin->save();
        
        $this->cache->clearTwigCache($this->skin);
        $this->cache->clear();
        return true;
    }
    
    public function installPlugin($pluginName)
    {
        $plugin = PluginsModel::where('plugin_name', $pluginName)->first();
        if ($plugin->install) {
            return false;
        }
        
        $pluginName = "\\Plugins\\$pluginName\\$pluginName";
        $info = $pluginName::info();
        $pluginVersion = explode('.', $info['boards_v']);
        $boardsVersion = explode('.', $this->version);
        
        switch ($pluginVersion[0]) {
            case '=':
            foreach ($boardsVersion as $k => $v) {
                if (intval($v) !== intval($pluginVersion[$k+1]) && $pluginVersion[$k+1] !== '*') {
                    return false;
                }
            }
            break;
            
            case '<':
            foreach ($boardsVersion as $k => $v) {
                if (intval($v) > intval($pluginVersion[$k+1]) && $pluginVersion[$k+1] !== '*') {
                    return false;
                }
            }
            break;
            
            case '>':
            foreach ($boardsVersion as $k => $v) {
                if (intval($v) < intval($pluginVersion[$k+1]) && $pluginVersion[$k+1] !== '*') {
                    return false;
                }
            }
            break;
   
        }
        $pluginName::install();
        $plugin->install = true;
        $plugin->save();
        
        $this->cache->clearTwigCache($this->skin);
        $this->cache->clear();
        return true;
    }
    
    public function uninstallPlugin($pluginName)
    {
        $plugin = PluginsModel::where('plugin_name', $pluginName)->first();
        if (!$plugin->install) {
            return false;
        }
        $pluginName = "\\Plugins\\$pluginName\\$pluginName" ;
        $pluginName::uninstall();
        $plugin->install = false;
        $plugin->save();
        
        $this->cache->clearTwigCache($this->skin);
        $this->cache->clear();
    }
}

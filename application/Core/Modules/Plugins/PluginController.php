<?php
declare(strict_types=1);


namespace Application\Core\Modules\Plugins;
use Illuminate\Database\Capsule\Manager as DB;

/**
*
*  
*
**/
class PluginController
{
	
	/**
	* The copy of Twig view	*
	* @var twig
	**/
	
    protected $twig;
	
	/**
	* The instance of Event Dispatcher
	* @var EventDispatcher
	**/
	
	protected $dispacher;
	
	/**
	* The instance of plugin loader
	* @var EventDispatcher
	**/
	
	protected $pluginLoader;

	/**
	*
	* @todo remove 45 line!
	*
	**/
	
	protected static $tplDir;
	
    public function __construct($container)
    {
		$this->dispacher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$this->globalEvent = new PluginGlobalEventController($container);
		$this->adminEvent = new PluginAdminEventController($container);
		$this->pluginLoader = new PluginLoaderController($container->get('cache'));
		self::$tplDir = $container->get('settings')['twig']['skin'];
		
		//($this->pluginLoader->reloadPluginsList()); // remove that in stable version
		
		if(is_array($this->pluginLoader->getPluginsList())){
			foreach ($this->pluginLoader->getPluginsList() as $val)
			{	
				if($val['active'] && class_exists('Plugins\\'.$val['plugin_name'].'\\'.$val['plugin_name']))
				{
					$pluginName = 'Plugins\\'. (string)$val['plugin_name'] .'\\'.(string)$val['plugin_name'];
					$this->dispacher->addSubscriber(new $pluginName()); 
				}					 
			}
		}
		
    }
	
	public function addGlobalEvent($eventName)
	{
		$this->dispacher->dispatch($eventName, $this->globalEvent);
	}
	
	public function addAdminEvent($eventName)
	{
		$this->dispacher->dispatch($eventName, $this->adminEvent);
	}
	
	public function getPluginLoader()
	{
		return $this->pluginLoader;
	}
	
	
	public static function addToTpl($template, $find, $html)
	{
		$tpl = MAIN_DIR . '/skins/' . $GLOBALS['tplDir'] . '/tpl/' . $template;
		
		$filecontent = file_get_contents($tpl);
		$pattern = '/('. $find .')(.*?)/s';
		$result = preg_replace($pattern,  $find.$html, $filecontent);

		return file_put_contents($tpl, $result);			
		
	}
	
	public static function  removeFromTpl($template, $find)
	{
		$tpl = MAIN_DIR . '/skins/' . $GLOBALS['tplDir'] . '/tpl/' . $template;
		$filecontent = file_get_contents($tpl);
		$pattern = '/('. $find .')(.*?)/s';
		$result = str_replace($find,  '', $filecontent);
		return file_put_contents($tpl, $result);			
		
	}
	
	public static function createTable()
	{
		$txt = 'CREATE TABLE `boards`.`test` ( `id` INT NOT NULL AUTO_INCREMENT , `a` INT NOT NULL , `b` INT NOT NULL , `c` INT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;';

		var_Dump(DB::statement($txt));
		
	}
	
}
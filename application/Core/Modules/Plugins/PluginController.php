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
		$this->pluginLoader = new PluginLoaderController($container->get('cache'),$container->get('settings')['twig']['skin']);
		self::$tplDir = $container->get('settings')['twig']['skin'];
		
		if(is_array($this->pluginLoader->getPluginsList())){
			foreach ($this->pluginLoader->getPluginsList() as $val)
			{	
				if(($val['active'] || isset($GLOBALS['admin'])) && class_exists('Plugins\\'.$val['plugin_name'].'\\'.$val['plugin_name']))
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
		$result = preg_replace($pattern,  $find."\n".$html."\n", $filecontent);

		return file_put_contents($tpl, $result);			
		
	}
	
	public static function  removeFromTpl($template, $find)
	{
		$tpl = MAIN_DIR . '/skins/' . $GLOBALS['tplDir'] . '/tpl/' . $template; 
		$filecontent = file_get_contents($tpl);
		$pattern = '/('. $find .')(.*?)/s';
		$result = str_replace("\n".$find."\n",  '', $filecontent);
		return file_put_contents($tpl, $result);			
		
	}
	
	public static function createTable($table, $query)
	{
		$db = require(MAIN_DIR . '/environment/Config/db_settings.php');
		$txt = 'CREATE TABLE `'.$db['database'].'`.`'. $db['prefix']. $table .'` ( '.$query.') ENGINE = InnoDB;';

		$return = DB::statement($txt);
		return $return;
	}
	
	public static function dropTable($table)
	{
		$db = require(MAIN_DIR . '/environment/Config/db_settings.php');
		$txt = 'DROP TABLE `'.$db['database'].'`.`'. $db['prefix']. $table . '`';
		$return = DB::statement($txt);
		return $return;
	}
	
}
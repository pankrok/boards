<?php

namespace Application\Core\Modules\Plugins;

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
	
    public function __construct($container)
    {
		$this->dispacher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$this->globalEvent = new PluginGlobalEventController($container);
		$this->adminEvent = new PluginAdminEventController($container);
		$this->pluginLoader = new PluginLoaderController($container->get('cache'));
		
		($this->pluginLoader->reloadPluginsList()); // remove that in stable version
		
		if(is_array($this->pluginLoader->getPluginsList())){
			foreach ($this->pluginLoader->getPluginsList() as $val)
			{	
				if($val['active'] && class_exists('Plugins\\'.$val['plugin_name']))
				{
					$pluginName = 'Plugins\\'. (string)$val['plugin_name'];
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
	

}
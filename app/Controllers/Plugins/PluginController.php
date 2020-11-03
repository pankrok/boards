<?php

namespace App\Controllers\Plugins;

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
			foreach ($this->pluginLoader->getPluginsList() as $key => $val)
			{	
				if($val['active'] && class_exists($key))
					 $this->dispacher->addSubscriber(new $key()); 	
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
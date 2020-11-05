<?php

namespace Application\Core\Modules\Plugins;

use Symfony\Component\EventDispatcher\Event;
use Slim\Routing\RouteContext;


/**
* Register 
*
**/

class PluginGlobalEventController extends Event
{
	
	/**
    *  @var twig view object
    **/
	
	
    protected $twig;
	protected $database;
	protected $auth;
	protected $_cache;
	protected $translator;
	
	/**
    * Constructor assign twig 
    * @return void
    **/
	
    public function __construct($container)
    {
        $this->twig = $container->get('view');
		$this->database = $container->get('db');
		$this->auth = $container->get('auth');
		$this->_cache = $container->get('cache');
		$this->translator = $container->get('translator');
    }
	
	/**
	* Add data to Twig Variable named plugin_{plugin_name}
	* @param $name name of Twig variable
	* @param $data data stored in Twig variable
	* @return void
	**/
	
	public function setTwigData($name, $data)
    {
		$this->twig->getEnvironment()->addGlobal('plugin_'.$name , $data);
    }
	
	/**
	* Find and replace string in Twig template
	* @param $tpl path to in skins/view/ of Twig template
	* @param $string string to search and replace in template
	* @param $replace news string
	* @return void
	**/
	
	public function translate()
	{
		return $this->translator;
	}
	
	public function cache()
	{
		return $this->_cache;
	}
	
	public function auth()
	{
		return $this->auth;
	}
	
	public function userdata()
	{
		return $this->auth->user();
	}
	
}
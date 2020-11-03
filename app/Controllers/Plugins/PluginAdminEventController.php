<?php

namespace App\Controllers\Plugins;

use Symfony\Component\EventDispatcher\Event;
use Slim\Routing\RouteContext;


/**
* Register 
*
**/

class PluginAdminEventController extends Event
{
	
	/**
    *  @var twig view object
    **/
    protected $twig;
	protected $database;
	protected $auth;
	
	/**
    * Constructor assign twig 
    * @return void
    **/
	
    public function __construct($container)
    {
        $this->twig = $container->get('view');
		$this->database = $container->get('db');
		$this->auth = $container->get('auth');
    }
	
	/**
	* Add data to Twig Variable named plugin_{plugin_name}
	* @param $name name of Twig variable
	* @param $data data stored in Twig variable
	* @return void
	**/
	
	public function setAdminTwigData($name, $data)
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

		
	public function findAndReplace($tpl, $string, $replace)
	{
		if(file_exists(MAIN_DIR.'/skins/'.$this->settings['skin'].'/'.$tpl && $container->get('auth')->user()->admin)){
			
			$content = file_get_contents(MAIN_DIR.'/skins/view/'.$tpl); 
			$replace = str_replace($string, $replace, $content);
			
			$file = fopen(MAIN_DIR.'/skins/'.$this->settings['skin'].'/'.$tpl, 'w+'); 
			fwrite($file, $replace);
			fclose($file);
		
		}
	}
	
	public function addTranslations($arr)
	{
		$file = MAIN_DIR.'/lang/'.$arr['lang'].'/'.$arr['name'].'.php';
		if(!file_exists($file) && $arr['name'] && $arr['lang']){
			$handler = fopen($file, 'w+');
			$txt = 
				"<?php\n\nreturn [\n\n";
			foreach($arr['translations'] as $k => $v)
			{
				$txt .= 
				"\t'$k' => '$v',\n";		
			}
			
			$txt = substr($txt, 0, -2);
			
			$txt .= "\n\n];";
			fwrite($handler, $txt);
			fclose($handler);
			
			return 'translations create!';
		}
		else
		{
			return 'error file '.$arr['file'].' exists!';
		}
		
	}
	
}
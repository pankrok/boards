<?php
declare(strict_types=1);
namespace Plugins;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Application\Core\Modules\Plugins\PluginController;

class ExamplePlugin implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return ['home.loaded' => 'MyFooPlugin',
				'admin.create.translations' => 'translations'];

    }

	public static function info()
	{
		return [
			'version' => '1.1',
			'boards_v' => '1.1.20',
			'author' => 'PanKrok',
			'website' => 's89.eu',
		];
		
	}
	
	public static function activation()
	{
		PluginController::addToTpl('home.twig', '<!-- plugin example -->', '{{ plugin_bar | raw }}');
	}
	
	public static function deactivation()
	{
		PluginController::removeFromTpl('home.twig', '{{ plugin_bar | raw }}');
		return 1;
	}
	
	public static function install()
	{
		PluginController::createTable();	
		return 1;
	}
	
	public static function remove($data)
	{
		return 1;
	}

    public function MyFooPlugin($data)
    {

	   $content = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
					 <strong>'.$data->translate()->trans('plugin.Holy guacamole!') .'</strong> '.$data->translate()->trans('plugin.This is a plugin example!') .'<br />
					  
					  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					  </button>
					</div>';
		
			
					
		$data->setTwigData('bar', $content);		
    }
	
	public function translations($data)
	{
		
		$trans = 
		[
			1=>['lang' => 'pl_PL',
				'name' => 'plugin',
				'translations' => [
					'Holy guacamole!' => 'Jasny gwint!',
					'This is a plugin example!' => 'To przykładowy plugin!'
				]
			],
			2=>['lang' => 'en_US',
				'name' => 'plugin',
				'translations' => [
					'Holy guacamole!' => 'Holy guacamole!',
					'This is a plugin example!' => 'This is a plugin example!'
				]
			]
		];		
		
		foreach($trans as $v){
			$data->addTranslations($v);
		}
		
	}
}

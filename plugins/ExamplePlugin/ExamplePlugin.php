<?php
declare(strict_types=1);
namespace Plugins\ExamplePlugin;

use Application\Core\Modules\Plugins\PluginInterface;
use Application\Core\Modules\Plugins\PluginController;

class ExamplePlugin implements PluginInterface
{

    public static function getSubscribedEvents()
    {
        return ['home.loaded' => 'MyFooPlugin'];

    }

	public static function info() : array
	{
		return [
			'version' => '1.1',
			'boards_v' => '1.1.20',
			'author' => 'PanKrok',
			'website' => 's89.eu',
		];
		
	}
	
	public static function activation() : bool
	{
		PluginController::addToTpl('home.twig', '<!-- boardstats -->', '{{ plugin_bar | raw }}');
		return true;
	}
	
	public static function deactivation() : bool
	{
		PluginController::removeFromTpl('home.twig', '{{ plugin_bar | raw }}');
		return true;
	}
	
	public static function install() : bool
	{
		return true;
	}
	
	public static function uninstall() : bool
	{
		return true;
	}

    public function MyFooPlugin($data)
    {
	   $content = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
					 <strong>'.$data->translate('Holy guacamole!') .'</strong> '.$data->translate('This is a plugin example!') .'<br />		  
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
		
	}
}

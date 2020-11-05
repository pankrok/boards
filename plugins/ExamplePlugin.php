<?php
  
namespace Plugins;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Controllers\Plugins\PluginEventController;

class ExamplePlugin implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return ['home.loaded' => 'MyFooPlugin',
				'admin.create.translations' => 'translations'];

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
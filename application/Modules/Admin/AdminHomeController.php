<?php

declare(strict_types=1);

namespace Application\Modules\Admin;
use Application\Core\Controller as Controller;

class AdminHomeController extends Controller
{
	
	public function index($request, $response, $arg)
	{

		return $this->adminView->render($response, 'home.twig');	;;	
	
	}
	
	
	public function plugin($request, $response, $arg)
	{
		$this->event->getPluginLoader()->installPlugin('ExamplePlugin');
		return $response;
	}
	
	public function pluginReload($request, $response)
	{
		$this->event->getPluginLoader()->reloadPluginsList();
		return $response;
	}
	
};


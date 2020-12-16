<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Plugins;
use Application\Core\Controller as Controller;

use Application\Models\PluginsModel;

class AdminPluginController extends Controller
{
	
	public function pluginList($request, $response, $arg)
	{
		$list = PluginsModel::get()->toArray();
		
		$this->adminView->getEnvironment()->addGlobal('plugins', $list);
		return $this->adminView->render($response, 'plugins.twig');
	}
	
	public function pluginControl($request, $response, $arg)
	{
		return $this->adminView->render($response, 'plugins_control.twig');
	}
	
	public function pluginInstall($request, $response)
	{
		$body = $request->getParsedBody();	
		$this->event->getPluginLoader()->installPlugin($body['plugin_name']);
		
		return $response->withHeader('Location', $this->router->urlFor('admin.plugins.get'))
		  ->withStatus(302);;
	}
	
	public function pluginActive($request, $response)
	{
		$body = $request->getParsedBody();	
		$this->event->getPluginLoader()->activePlugin($body['plugin_name']);
		
		return $response->withHeader('Location', $this->router->urlFor('admin.plugins.get'))
		  ->withStatus(302);;
	}
	
	public function pluginUninstall($request, $response)
	{
		$body = $request->getParsedBody();	
		$this->event->getPluginLoader()->uninstallPlugin($body['plugin_name']);
		
		return $response->withHeader('Location', $this->router->urlFor('admin.plugins.get'))
		  ->withStatus(302);;
	}
	
	public function pluginDective($request, $response)
	{
		$body = $request->getParsedBody();	
		$this->event->getPluginLoader()->deactivePlugin($body['plugin_name']);
		
		return $response->withHeader('Location', $this->router->urlFor('admin.plugins.get'))
		  ->withStatus(302);;
	}
	
	public function pluginReload($request, $response)
	{
		$this->event->getPluginLoader()->reloadPluginsList();
		return $response->withHeader('Location', $this->router->urlFor('admin.plugins.get'))
		  ->withStatus(302);;
	}
	
};


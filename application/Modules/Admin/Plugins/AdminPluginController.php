<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Plugins;

use Application\Core\Controller as Controller;

use Application\Models\PluginsModel;

class AdminPluginController extends Controller
{
    public function pluginList($request, $response, $arg)
    {
        $this->event->getPluginLoader()->reloadPluginsList();
        
        $list = PluginsModel::get()->toArray();
        foreach ($list as $k => $v) {
            $h = $v['plugin_name'];
            $name = "\\Plugins\\$h\\$h";
            $list[$k]['info'] = $name::info();
        }

        $this->adminView->getEnvironment()->addGlobal('plugins', $list);
        return $this->adminView->render($response, 'plugins.twig');
    }
    
    public function pluginControl($request, $response, $arg)
    {
        global $params;
        $params = [
            '1' => $arg['param1'] ?? null,
            '2' => $arg['param2'] ?? null
        ];
        
        $this->container->get('event')->addGlobalEvent('plugin.contoller.'.$arg['pluginName']);
        $this->adminView->getEnvironment()->addGlobal('params', $params);
        $this->adminView->getEnvironment()->addGlobal('pluginName', $arg['pluginName']);
        return $this->adminView->render($response, 'plugin_ext.twig');
    }
    
    public function pluginInstall($request, $response)
    {
        $body = $request->getParsedBody();
        $this->event->getPluginLoader()->installPlugin($body['plugin_name']);
        
        return $response->withHeader('Location', $this->router->urlFor('admin.plugins.get'))
          ->withStatus(302);
        ;
    }
    
    public function pluginActive($request, $response)
    {
        $body = $request->getParsedBody();
        $this->event->getPluginLoader()->activePlugin($body['plugin_name']);
        
        return $response->withHeader('Location', $this->router->urlFor('admin.plugins.get'))
          ->withStatus(302);
        ;
    }
    
    public function pluginUninstall($request, $response)
    {
        $body = $request->getParsedBody();
        $this->event->getPluginLoader()->uninstallPlugin($body['plugin_name']);
        
        return $response->withHeader('Location', $this->router->urlFor('admin.plugins.get'))
          ->withStatus(302);
        ;
    }
    
    public function pluginDective($request, $response)
    {
        $body = $request->getParsedBody();
        $this->event->getPluginLoader()->deactivePlugin($body['plugin_name']);
        
        return $response->withHeader('Location', $this->router->urlFor('admin.plugins.get'))
          ->withStatus(302);
        ;
    }
};

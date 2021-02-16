<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Settings;

use Application\Core\AdminController as Controller;
use Application\Core\Modules\Configuration\ConfigurationCore;
use Application\Models\MenuModel;
use Application\Models\PagesModel;

class AdminMenuController extends Controller
{
    public function index($request, $response)
    {
        $menu = MenuModel::get()->toArray();     
        $this->adminView->getEnvironment()->addGlobal('menu', $menu);
        $this->adminView->getEnvironment()->addGlobal('show_settings', true);
        return $this->adminView->render($response, 'menu_controller.twig');
    }
    
    public function manageItem($request, $response, $arg)
    {
        $body = $request->getParsedBody();
        if (isset($arg['id'])) {
            $menu = MenuModel::find($arg['id']);
            $this->adminView->getEnvironment()->addGlobal('menu', $menu);
        } else {
            $links = PagesModel::get()->toArray();
            foreach ($links as $k => $v) {
                if ($v['system']) {
                    $links[$k]['url'] = $this->router->urlFor($v['system']);
                } else {
                    $links[$k]['url'] = $this->router->urlFor('page', ['id' => $v['id']]);
                }
            }
            
            $this->adminView->getEnvironment()->addGlobal('links', $links);
        }
        
        if (isset($body['own_name']) && isset($body['own_url'])) {
           
            $menu = MenuModel::create([
                'url' => $body['own_url'],
                'name' => $body['own_name'],
                'url_order' => $body['url_order']
                ]);
            return $response
                ->withHeader('Location', $this->router->urlFor('admin.menu.manage', ['id' => $menu->id]))
                ->withStatus(302);
        }
        
        if (isset($body['id'])) {
            $menu = MenuModel::find($body['id']);
            $menu->url_order = $body['url_order'];
            $menu->save();

            return $response
                ->withHeader('Location', $this->router->urlFor('admin.menu.manage', ['id' => $menu->id]))
                ->withStatus(302);
        }
        
        if (isset($body['name']) && !isset($body['id'])) {
            $body['name'] = explode(';', $body['name']);
            $menu = MenuModel::create([
                'url' => $body['name'][0],
                'name' => $body['name'][1],
                'url_order' => $body['url_order']
                ]);
            return $response
                ->withHeader('Location', $this->router->urlFor('admin.menu.manage', ['id' => $menu->id]))
                ->withStatus(302);
        }
        
        $this->adminView->getEnvironment()->addGlobal('show_settings', true);
        return $this->adminView->render($response, 'menu_edit.twig');
    }
    
    public function deleteItem($request, $response)
    {
        $body = $request->getParsedBody();
        
        MenuModel::find($body['id'])->delete();
        
        return $response
                ->withHeader('Location', $this->router->urlFor('admin.menu'))
                ->withStatus(302);
    }
}

<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Settings;

use Application\Core\AdminController as Controller;
use Application\Core\Modules\Configuration\ConfigurationCore;
use Application\Models\GroupsModel;

class AdminSettingsController extends Controller
{
    public function index($request, $response)
    {
        $this->adminView->getEnvironment()->addGlobal('settings', $this->settings);
        $this->adminView->getEnvironment()->addGlobal('groups', GroupsModel::get()->toArray());
        $this->adminView->getEnvironment()->addGlobal('show_settings', true);
        return $this->adminView->render($response, 'settings.twig');
    }
    
    public function saveSettings($request, $response)
    {
        if (ConfigurationCore::saveConfig($request->getParsedBody())) {
            $this->flash->addMessage('success', 'configuration updated.');
        } else {
            $this->flash->addMessage('danger', 'configuration update error.');
        }
        return $response
                ->withHeader('Location', $this->router->urlFor('admin.get.settings'))
                ->withStatus(302);
    }
    
    public function mailer($request, $response)
    {
        $dir = MAIN_DIR . '/environment/Config/mail.json';
        $method = $request->getMethod();
        
        if ($method === "POST") {
            $body = $request->getParsedBody();
            file_put_contents($dir, json_encode($body, JSON_PRETTY_PRINT));
        }
        
        $mailCfg = json_decode(file_get_contents($dir), true);
        $this->adminView->getEnvironment()->addGlobal('mail_cfg', $mailCfg);
        $this->adminView->getEnvironment()->addGlobal('show_settings', true);
        return $this->adminView->render($response, 'mail_settings.twig');
    }
    
    public function cleanCache($request, $response)
    {
        $body = $request->getParsedBody();
        $message = '';
        if (isset($body['objects'])) {
            $this->cache->clearCache();
            $message .= $this->translator->get('admin.object cache removed');
        }
        if (isset($body['skins'])) {
            $this->cache->cleanAllSkinsCache();
            if (isset($body['objects'])) {
                $message .= ', ';
            }
            $message .=  $this->translator->get('admin.skins cache removed');
        }
        if (isset($body['skins']) || isset($body['objects'])) {
            $this->flash->addMessage('info', $message);
        }
        
        $this->adminView->getEnvironment()->addGlobal('show_settings', true);     
        return $this->adminView->render($response, 'cache.twig');
    }
}

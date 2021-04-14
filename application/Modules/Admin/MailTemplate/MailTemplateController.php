<?php

declare(strict_types=1);

namespace Application\Modules\Admin\MailTemplate;

use Application\Core\AdminController as Controller;

class MailTemplateController extends Controller
{
    public function index($request, $response)
    {
        $templateList = array_diff(scandir(MAIN_DIR . '/public/mails'), ['.','..']);
        $this->adminView->getEnvironment()->addGlobal('list', $templateList);
        $this->adminView->getEnvironment()->addGlobal('show_settings', true);
                
        return $this->adminView->render($response, 'mailTemplates/list.twig');
    }
    
    public function editMailTemplate($request, $response, $arg)
    {
        if (file_exists(MAIN_DIR . '/public/mails/' . $arg['twig'])) {
            $template = file_get_contents(MAIN_DIR . '/public/mails/' . $arg['twig']);
            $this->adminView->getEnvironment()->addGlobal('file', $arg['twig']);
            $this->adminView->getEnvironment()->addGlobal('content', $template);
        }
        
        $this->adminView->getEnvironment()->addGlobal('show_settings', true);
        return $this->adminView->render($response, 'mailTemplates/edit.twig');
    }
    
    public function saveMailTemplate($request, $response)
    {
        $body = $request->getParsedBody();
        $fileDir = MAIN_DIR . '/public/mails/' . $body['twig'];
        file_put_contents($fileDir, $body['desc']);
        
        return $response->withHeader('Location', $this->router->urlFor('admin.mail.template.edit', ['twig' => $body['twig']]))
                ->withStatus(302);
    }
}

<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Board;

use Application\Core\Controller as Controller;

use Application\Models\PagesModel;

class AdminPagesController extends Controller
{
    public function index($request, $response, $arg)
    {
        $this->adminView->getEnvironment()->addGlobal('pages', PagesModel::get());
        return $this->adminView->render($response, 'pages.twig');
    }
    
    public function managePage($request, $response, $arg)
    {
        $body = $request->getParsedBody();
        if (isset($arg['id'])) {
            $data = PagesModel::find($arg['id'])->toArray();
            $this->adminView->getEnvironment()->addGlobal('data', $data);
        }
        if (isset($body['id'])) {
            $data = PagesModel::find($body['id']);
            $data->name = $body['name'];
            $data->content = $body['content'];
            $data->active = $body['active'] ?? false;
            $data->save();
            $this->adminView->getEnvironment()->addGlobal('data', $data);
        }
        
        if (isset($body['name']) && !isset($body['id'])) {
            $data = PagesModel::create([
                'name' => $body['name'],
                'content' => $body['content'],
                'active' => $body['active'] ?? false
                ]);
            
            return $response
              ->withHeader('Location', $this->router->urlFor('admin.page.edit', ['id' => $data->id]))
              ->withStatus(302);
            ;
        }
        
        return $this->adminView->render($response, 'edit_page.twig');
    }
    
    public function updateInsertPage($request, $response)
    {
        return $response;
    }
    
    public function deletePage($request, $response)
    {
        $data = $request->getParsedBody();
        $page = PagesModel::find($data['id']);
        if ($page->system) {
            $message = ['warning', 'You cant delete system pages!'];
        } else {
            $message = ['success', 'page id: '. $page->id .' deleted'];
            $page->delete();
        }
        
        $this->flash->addMessage($message[0], $message[1]);
        return $response->withHeader('Location', $this->router->urlFor('admin.pages'))
              ->withStatus(302);
    }
}

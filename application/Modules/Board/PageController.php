<?php

declare(strict_types=1);

namespace Application\Modules\Board;

use Application\Core\Controller as Controller;
use Application\Models\PagesModel;

class PageController extends Controller
{
    public function page($request, $response, $arg)
    {
        $content = $request->getAttribute('cache');
        if (!isset($content)) {
            $routeContext  = \Slim\Routing\RouteContext::fromRequest($request);
            $name = $routeContext->getRoute()->getName();
            $routeName = $routeContext->getRoutingResults()->getUri();
            $this->cache->setName($name);
            
            $content = PagesModel::find($arg['id'])->toArray();
            $this->cache->store($routeName, $content);
        }
        
        $this->view->getEnvironment()->addGlobal('page', $content);
        return $this->view->render($response, 'page.twig');
        ;
    }
}

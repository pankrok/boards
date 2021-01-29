<?php

/**
*
*	Box Modules Middleware
*
**/

declare(strict_types=1);

namespace Application\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

use Application\Models\SkinsModel;
use Application\Models\SkinsBoxesModel;

class ModulesMiddleware extends Middleware
{
    public function __invoke(Request $request, RequestHandler $handler)
    {
        $routeArray = json_decode(file_get_contents(MAIN_DIR . '/environment/Config/box_cache.json'), true);
        
        $routeContext  = \Slim\Routing\RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $name = $route->getName();

        if (explode('.', $name)[0] === 'admin' && !in_array($name, $routeArray)) {
            return $handler->handle($request);
        }
        
        $this->container->get('cache')->setName('box-controller');
        if (!$boxes = $this->container->get('cache')->receive($name)) {
            $positions = ['top', 'left', 'right', 'bottom'];
            $activeSkin = SkinsModel::where('active', 1)->select('id')->first()->toArray()['id'];
        
            foreach ($positions as $position) {
                $boxes[$position] = SkinsBoxesModel::where([['side', $position], ['skin_id', $activeSkin]])
                                ->leftJoin('boxes', 'skins_boxes.box_id', 'boxes.id')
                                ->leftJoin('costum_boxes', 'boxes.costum_id', 'costum_boxes.id')
                                ->select('skins_boxes.side', 'skins_boxes.box_order', 'skins_boxes.active', 'skins_boxes.hide_on_mobile', 'boxes.costum_id', 'boxes.engine', 'costum_boxes.translate', 'costum_boxes.name_prefix', 'costum_boxes.name', 'costum_boxes.html')
                                ->orderBy('skins_boxes.box_order', 'desc')->get()->toArray();
                
                
                foreach ($boxes[$position] as $k => $v) {
                    if (isset(json_decode($v['active'], true)[$name])) {
                        $active = json_decode($v['active'], true)[$name];
                        if ($active) {
                            unset($boxes[$position][$k]['active']);
                            if ($v['translate']) {
                                $boxes[$position][$k]['name'] = $this->container->get('translator')->get('lang.'.$v['name']);
                            }
                        } else {
                            unset($boxes[$position][$k]);
                        }
                    } else {
                        unset($boxes[$position][$k]);
                    }
                }
            }
                            
            $this->container->get('cache')->store($name, $boxes, 0);
        }
        $this->container->get('view')->getEnvironment()->addGlobal('modules', $boxes);
        return $handler->handle($request);
    }
}

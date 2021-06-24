<?php

/**
*
*	Ceche Middleware
*
**/

declare(strict_types=1);

namespace Application\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class CacheMiddleware extends Middleware
{
    public function __invoke(Request $request, RequestHandler $handler)
    {
        $cache = $this->container->get('cache');
        $routeContext  = \Slim\Routing\RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $name = $route->getName();
        $this->container->get('adminView')->getEnvironment()->addGlobal('route_name', $name);
        
        if (explode('.', $name)[0] === 'admin' && $request->getMethod() == 'POST') {
            $cache->clear();
            return $handler->handle($request);
        }
        
        if ($request->getMethod() !== 'GET') {
            return $handler->handle($request);
        }
        
        $routeName = $routeContext->getRoutingResults()->getUri();
        $cache->setPath($name);
        if (!$cacheData = $cache->get($routeName)) {
            $cacheData = null;
        }
        
        $request = $request->withAttribute('cache', $cacheData);
        
        return $handler->handle($request);
    }
}

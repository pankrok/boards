<?php

/**
*
*	This is an example Before Middleware
*
**/

declare(strict_types=1);

namespace Application\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

class TrailingShashMiddleware extends Middleware
{
    public function __invoke(Request $request, RequestHandler $handler)
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        
        if ($route->getName() == 'home') {
            return $handler->handle($request);
        }
        
        if ($path != '/' && substr($path, -1) == '/') {
            // recursively remove slashes when its more than 1 slash
            $path = rtrim($path, '/');

            // permanently redirect paths with a trailing slash
            // to their non-trailing counterpart
            $uri = $uri->withPath($path);
            
            if ($request->getMethod() == 'GET') {
                $response = new Response();
                return $response
                    ->withHeader('Location', (string) $uri)
                    ->withStatus(301);
            } else {
                $request = $request->withUri($uri);
            }
        }

        return $handler->handle($request);
    }
}

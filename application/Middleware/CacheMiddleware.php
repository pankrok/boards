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
	
	public function __invoke (Request $request, RequestHandler $handler) {
			
		$routeName = \Slim\Routing\RouteContext::fromRequest($request)->getRoutingResults()->getUri();
		$cache = $this->container->get('cache');
		
		if(!$cacheData = $cache->receive($routeName))
			$cacheData = null;
		
		$request = $request->withAttribute('cache', $cacheData);
		
		return $handler->handle($request);
	}
}
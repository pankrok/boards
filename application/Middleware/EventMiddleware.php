<?php

/**
*
*	Event Middleware
*
**/

declare(strict_types=1);

namespace Application\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class EventMiddleware extends Middleware
{
	
	public function __invoke (Request $request, RequestHandler $handler) {
			
		$routeContext  = \Slim\Routing\RouteContext::fromRequest($request);
		$route = $routeContext->getRoute();
		if (empty($route)) {
			throw new HttpNotFoundException($request);
		}
		$name = $route->getName();
		if(substr($name, 0, 5) !== 'admin')
		{		
			$this->container->get('event')->addGlobalEvent('global.event');
			$this->container->get('event')->addGlobalEvent($name);		
		}
		
		return $handler->handle($request);
	}
}
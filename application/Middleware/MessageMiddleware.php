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

class MessageMiddleware extends Middleware
{
	
	public function __invoke (Request $request, RequestHandler $handler) {
		
		if(isset($_SESSION['errors'])) $this->container->get('view')->getEnvironment()->addGlobal('errors', $_SESSION['errors']);
        unset($_SESSION['errors']);
		
		return $handler->handle($request);
	}
}
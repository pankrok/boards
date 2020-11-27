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
		
		if(isset($_SESSION['message']))
		{
			$this->container->get('view')->getEnvironment()->addGlobal('message', $_SESSION['message']);
			$_SESSION['message'] = NULL;
		}
		if(isset($_SESSION['adminMessage']))
		{
			$this->container->get('adminView')->getEnvironment()->addGlobal('message', $_SESSION['adminMessage']);
			$_SESSION['adminMessage'] = NULL;
		}
		return $handler->handle($request);
	}
}
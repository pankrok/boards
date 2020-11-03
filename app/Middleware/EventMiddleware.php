<?php

namespace App\Middleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class EventMiddleware extends Middleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {

		if(isset($_SESSION['event'])) $this->container->get('event')->addGlobalEvent($_SESSION['event']);
		if(isset($_SESSION['adminEvent'])) $this->container->get('event')->addAdminEvent($_SESSION['adminEvent']);
		unset($_SESSION['event']);
		unset($_SESSION['adminEvent']);
        
		$response = new Response();    
		$response = $handler->handle($request);
		
        return $response;
    }
}
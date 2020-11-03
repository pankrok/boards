<?php

namespace App\Middleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class ValidationErrorsMiddleware extends Middleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        
		
		if(isset($_SESSION['errors'])) $this->container->get('view')->getEnvironment()->addGlobal('errors', $_SESSION['errors']);
		unset($_SESSION['errors']);
        
		$response = new Response();    
		$response = $handler->handle($request);
		
        return $response;
    }
}
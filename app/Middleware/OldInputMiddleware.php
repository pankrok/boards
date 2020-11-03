<?php

namespace App\Middleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class OldInputMiddleware extends Middleware
{
	public function __invoke(Request $request, RequestHandler $handler): Response
    {  
		if(!isset($_SESSION['old'])){
            $_SESSION['old'] = true;    
        }
        else
        {
			$this->container->get('view')->getEnvironment()->addGlobal('old', $_SESSION['old']);		
			if($request->getParsedBody()) $_SESSION['old'] = $request->getParsedBody();	
        }

	$response = new Response();    
    $response = $handler->handle($request);

    return $response;
    }
}
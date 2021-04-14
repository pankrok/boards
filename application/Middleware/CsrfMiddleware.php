<?php

namespace Application\Middleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class CsrfMiddleware extends Middleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
		if (false === $request->getAttribute('csrf_status')) 
		{
			$response = new Response();    
			$response = $handler->handle($request);
			echo('Yay, you broke it!'); die(); // add CSRF error handler
		
			return $response;
		} 
		else 
		{
			$token = ($this->container->get('csrf')->generateToken());
			$this->container->get('view')->getEnvironment()->addGlobal('csrf', [
				'field' => '
				<input id="csrf_name" type="hidden" name="'. $this->container->get('csrf')->getTokenNameKey() .'" value="'. $token['csrf_name'] .'">
				<input id="csrf_value" type="hidden" name="'. $this->container->get('csrf')->getTokenValueKey() .'" value="'. $token ['csrf_value'] .'">
				',
			]);
			
			$response = new Response();    
			$response = $handler->handle($request);
			
			return $response;
		}
    }
}
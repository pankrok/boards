<?php

/**
*
*	This is an example After Middleware
*
**/

declare(strict_types=1);

namespace Application\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class After extends Middleware
{
	
	public function __invoke (Request $request, RequestHandler $handler) {
		
		$response = $handler->handle($request);
		$response->getBody()->write('AFTER');
		return $response;
	}

};
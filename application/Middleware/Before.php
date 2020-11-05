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

class Before extends Middleware
{
	
	public function __invoke (Request $request, RequestHandler $handler) {
		$response = $handler->handle($request);
		$existingContent = (string) $response->getBody();

		$response = new Response();
		$response->getBody()->write('BEFORE' . $existingContent);

		return $response;
	}
}
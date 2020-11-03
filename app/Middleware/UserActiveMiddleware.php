<?php

namespace App\Middleware;

use App\Models\UserModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class UserActiveMiddleware extends Middleware
{
	public function __invoke(Request $request, RequestHandler $handler): Response
    {  
		if(isset($_SESSION['user'])){
            $user = UserModel::find($_SESSION['user']);
			$user->last_active = time();
			$user->save();
        }

	$response = new Response();    
    $response = $handler->handle($request);

    return $response;
    }
}
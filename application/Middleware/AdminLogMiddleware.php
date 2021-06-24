<?php

/**
*
*	Admin Logs Middleware
*
**/

declare(strict_types=1);

namespace Application\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Application\Models\AdminLogModel;

class AdminLogMiddleware extends Middleware
{
    public function __invoke(Request $request, RequestHandler $handler)
    {
        if ($request->getMethod() === 'POST') {
            $routeContext  = \Slim\Routing\RouteContext::fromRequest($request);
            $name = $routeContext->getRoute()->getName();
            if (strpos($name, 'admin.') !== false) {
                AdminLogModel::create([
                    'admin_id' =>  $_SESSION['user'],
                    'log' => json_encode([
                        'request' => $request->getParsedBody(),
                        'route' => $name
                    ])
                ]);
            }
        }
        
        return $handler->handle($request);
    }
}

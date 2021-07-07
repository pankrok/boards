<?php

/**
*
*
*
**/

declare(strict_types=1);

namespace Application\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class OldInputMiddleware extends Middleware
{
    public function __invoke(Request $request, RequestHandler $handler)
    {
        if (!isset($_SESSION['old'])) {
            $_SESSION['old'] = true;
        } else {
            $this->container->get('view')->getEnvironment()->addGlobal('old', $_SESSION['old']);
            $_SESSION['old'] = $request->getParsedBody() ?? true;
        }

        return $handler->handle($request);
    }
}

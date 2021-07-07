<?php
declare(strict_types=1);

namespace Application\Interfaces\Messages;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

interface MessengerInterface
{

    public function get(Request $request, Response $response, array $arg) : Response;
    public function post(Request $request, Response $response) : Response;
    public function list(Request $request, Response $response, array $arg) : Response;
    public function find(Request $request, Response $response) : Response;
    
}

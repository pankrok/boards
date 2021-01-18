<?php

namespace Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
* Middleware constructor
*
* @package BOARDS Forum
* @since   0.1
**/

class Middleware
{
    
    /**
    * The copy of application container
    *
    * @var object
    **/
    
    protected $container;
    
    /**
    * Default constructor of all controllers
    *
    * @param object of main container $config
    * @return void
    **/
    
    public function __construct($container)
    {
        $this->container = $container;
    }
}

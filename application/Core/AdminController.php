<?php

declare(strict_types=1);

namespace Application\Core;

use Psr\Container\ContainerInterface;

class AdminController
{
    protected $container;
    
    public function __construct($c)
    {
        if ($c->get('auth')->checkAdmin() < 1) {
            echo $c->get('explorer')->showError(
                'Unauthorized',
                401,
                'Access to this resource is denied your client has not supplied the correct authentication.'
            );
            die();
        }
        
        $this->container = $c;
    }
    
    public function __get($property)
    {
        if ($this->container->get($property)) {
            return $this->container->get($property);
        }
    }
    
    protected function csftToken()
    {
        $token = ($this->container->get('csrf')->generateToken());
        return [
            'csrf_name' => $token['csrf_name'],
            'csrf_value' => $token['csrf_value']
        ];
    }
    
    protected function base_url($domainOnly = false)
    {
        if (isset($_SERVER['HTTPS'])) {
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        } else {
            $protocol = 'http';
        }
        if ($domainOnly === true) {
            return $protocol . "://" . $_SERVER['HTTP_HOST'];
        }
        
        return $protocol . "://" . $_SERVER['HTTP_HOST'] . substr($this->router->urlFor('home'), 0, -1);
    }
}

<?php

namespace Application\Core\Modules\Views\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class OnlineExtension extends AbstractExtension
{
    protected $OnlineController;
    
    public function __construct($controller)
    {
        $this->OnlineController = $controller;
    }
        
    public function getFunctions()
    {
        return 	[
            new TwigFunction('isOnline', [$this, 'isOnline']),
        ];
    }
    
    
    public function isOnline($id)
    {
        $arr = $this->OnlineController->getOnline();
        isset($arr[$id]) ? $return = true : $return = false;
        
        return $return;
    }
}

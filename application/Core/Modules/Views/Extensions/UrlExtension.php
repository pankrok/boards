<?php

namespace Application\Core\Modules\Views\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UrlExtension extends AbstractExtension
{
    protected $base_url;
    protected $router;
    protected $urlMaker;
    
    public function __construct($router, $urlMaker)
    {
        $this->base_url = substr($router->urlFor('home'), 0, -1);
        $this->router = $router;
        $this->urlMaker = $urlMaker;
    }
    
    public function getFunctions()
    {
        return 	[
            new TwigFunction('base_url', [$this, 'base_url']),
            new TwigFunction('path_for', [$this, 'path_for']),
            new TwigFunction('json_decode', [$this, 'jsonDecode']),
            new TwigFunction('json_encode', [$this, 'jsonEncode']),
            new TwigFunction('toUrl', [$this, 'toUrl']),
            new TwigFunction('stripTags', [$this, 'stripTags']),
            new TwigFunction('avatar', [$this, 'avatar'])
        ];
    }
    
    
    public function base_url()
    {
        return $this->base_url;
    }
    
    public function path_for($path, $arg = [])
    {
        return $this->router->urlFor($path, $arg);
    }
    
    public function jsonDecode($arg)
    {
        return json_decode($arg, true);
    }
    
    public function jsonEncode($arg)
    {
        return json_encode($arg);
    }
    
    public function toUrl($arg)
    {
        return $this->urlMaker->toUrl($arg);
    }
    
    public function stripTags($arg)
    {
        return strip_tags($arg);
    }
    
    public function avatar($img)
    {
        if (isset($img)) {
           $img =  $this->base_url . '/public/upload/avatars/'.$img;
        } else {
            $img =  $this->base_url . '/public/img/avatar.png';
        }
        return $img;
    }
}

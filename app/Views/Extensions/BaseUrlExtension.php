<?php

namespace App\Views\Extensions;

use Twig_Extension;
use Twig_SimpleFunction;

class BaseUrlExtension extends Twig_Extension
{
	
	protected $base_url;
	protected $router;
	protected $urlMaker;
	
	public function __construct($router, $urlMaker)
	{
		if(isset($_SERVER['HTTPS'])){
			$protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
		}
		else{
			$protocol = 'http';
		}
		
		$this->base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . PREFIX;
		$this->router = $router;
		$this->urlMaker = $urlMaker;
	}
	
	public function getFunctions()
	{
		return 	[
			new Twig_SimpleFunction('base_url', [$this, 'base_url']),
			new Twig_SimpleFunction('path_for', [$this, 'path_for']),
			new Twig_SimpleFunction('urlFor', [$this, 'urlFor']),
			new Twig_SimpleFunction('toUrl', [$this, 'toUrl'])
		];
		
	}
	
	
	public function base_url()
	{
		return $this->base_url;
	}
	
	public function path_for($arg)
	{
		return $this->router->urlFor($arg);
	}
	
	public function urlFor($arg)
	{
		return $this->router->urlFor($arg);
	}
	
	public function toUrl($arg)
	{
		return $this->urlMaker->toUrl($arg);
	}
	
}
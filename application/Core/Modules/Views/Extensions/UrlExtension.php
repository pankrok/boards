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
	#		new TwigFunction('urlFor', [$this, 'urlFor']),
			new TwigFunction('toUrl', [$this, 'toUrl'])
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
	
	public function urlFor($arg)
	{
		return $this->router->urlFor($arg); // deprecated
	}
	
	public function toUrl($arg)
	{
		return $this->urlMaker->toUrl($arg);
	}
	
}
<?php

namespace App\Views\Extensions;

use Illuminate\Translation\Translator;
use Twig_Extension;
use Twig_SimpleFunction;

class TranslationExtension extends Twig_Extension
{
	
	protected $translator;
	
	public function __construct(Translator $translator)
	{
		$this->translator = $translator;
	}
	
	public function getFunctions()
	{
		return 	[
			new Twig_SimpleFunction('trans', [$this, 'trans']),
			new Twig_SimpleFunction('locale', [$this, 'locale'])
		];
		
	}
	
	public function trans($key)
	{
		$result = substr($key, 4, 1);
		if($result == '.') 
			return $this->translator->trans($key);
		return $this->translator->trans('lang.'.$key);
	}
	
	public function locale()
	{
		return $this->translator->getLocale();
	}
	
}
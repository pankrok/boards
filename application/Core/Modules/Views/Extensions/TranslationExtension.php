<?php

namespace Application\Core\Modules\Views\Extensions;

use Illuminate\Translation\Translator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TranslationExtension extends AbstractExtension
{
    protected $translator;
    
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }
    
    public function getFunctions()
    {
        return 	[
            new TwigFunction('trans', [$this, 'trans']),
            new TwigFunction('locale', [$this, 'locale'])
        ];
    }
    
    public function trans($key)
    {
        return $this->translator->get($key);
    }
    
    public function locale()
    {
        return $this->translator->getLocale();
    }
}

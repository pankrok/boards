<?php

namespace Application\Core\Modules\Views\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UnreadExtension extends AbstractExtension
{
    protected $unreadController;

    
    public function __construct($controller)
    {
        $this->unreadController = $controller;
    }
    
    public function getFunctions()
    {
        return 	[
            new TwigFunction('unread_plot', [$this, 'unread_plot']),
            new TwigFunction('unread_board', [$this, 'unread_board']),
        ];
    }
    
    
    public function unread_plot($id)
    {
        return $this->unreadController->isUnreadPlot($id);
    }

    public function unread_board($id)
    {
        return $this->unreadController->isUnreadBoard($id);
    }
}

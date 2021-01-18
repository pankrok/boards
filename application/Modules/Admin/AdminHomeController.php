<?php

declare(strict_types=1);

namespace Application\Modules\Admin;

use Application\Core\Controller as Controller;

class AdminHomeController extends Controller
{
    public function index($request, $response, $arg)
    {
        return $this->adminView->render($response, 'home.twig');
        ;
        ;
    }
};

<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Updater;

use Application\Core\AdminController as Controller;

class AdminUpdateController extends Controller
{
    public function index($request, $response)
    {
        $this->adminView->getEnvironment()->addGlobal('version', base64_decode($this->settings['core']['version'], true));
        $this->adminView->getEnvironment()->addGlobal('show_settings', true);
        return $this->adminView->render($response, 'update.twig');
    }
}

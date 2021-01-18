<?php

declare(strict_types=1);

namespace Application\Modules\Admin\Updater;

use Application\Core\Controller as Controller;

class AdminUpdateController extends Controller
{
    public function index($request, $response)
    {
        $this->adminView->getEnvironment()->addGlobal('version', base64_decode($this->settings['core']['version']));
        return $this->adminView->render($response, 'update.twig');
    }
}

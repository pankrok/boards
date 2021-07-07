<?php

declare(strict_types=1);

namespace Application\Modules\Skins;

use Application\Core\Controller as Controller;
use Application\Modules\Skins;

class SkinController extends Controller
{
    public function change($request, $response, $arg)
    {
        if (is_dir(MAIN_DIR . '/skins/' . $arg['skin'])) {
            $_SESSION['skin'] = $arg['skin'];
        }
        
        return $response
          ->withHeader('Location', $this->router->urlFor('home'))
          ->withStatus(302);
    }
};

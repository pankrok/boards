<?php

declare(strict_types=1);

namespace Application\Modules\Board;

use Application\Models\MenuModel;

class MenuController
{
    public static function getMenu() : array
    {
        return MenuModel::orderBy('url_order', 'desc')->get()->toArray();
    }
}

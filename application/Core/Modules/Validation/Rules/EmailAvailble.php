<?php

declare(strict_types=1);

namespace Application\Core\Modules\Validation\Rules;

use Application\Models\UserModel;
use Respect\Validation\Rules\AbstractRule;

final class EmailAvailble extends AbstractRule
{
    public function validate($input) : bool
    {
        return UserModel::where('email', $input)->count() === 0;
    }
}

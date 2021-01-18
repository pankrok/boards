<?php

declare(strict_types=1);

namespace Application\Core\Modules\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class EmailAvailbleException extends ValidationException
{
    public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Ten email jest już w bazie.',
        ],
    ];
}

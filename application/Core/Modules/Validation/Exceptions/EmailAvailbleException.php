<?php

declare(strict_types=1);

namespace Application\Core\Modules\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

final class EmailAvailbleException extends ValidationException
{
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'This email is in database',
        ]
    ];
}

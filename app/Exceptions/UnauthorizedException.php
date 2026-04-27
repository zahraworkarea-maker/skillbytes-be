<?php

namespace App\Exceptions;

/**
 * Exception thrown for unauthorized access
 */
class UnauthorizedException extends CustomException
{
    public function __construct(string $message = "Unauthorized access")
    {
        parent::__construct(
            $message,
            401,
            "UNAUTHORIZED"
        );
    }
}

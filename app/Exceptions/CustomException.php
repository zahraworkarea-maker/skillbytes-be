<?php

namespace App\Exceptions;

use Exception;

/**
 * Custom Exception for API errors
 */
class CustomException extends Exception
{
    public $statusCode;
    public $errorCode;

    public function __construct(
        string $message = "Error",
        int $statusCode = 400,
        string $errorCode = "ERROR",
        int $code = 0
    ) {
        parent::__construct($message, $code);
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }
}

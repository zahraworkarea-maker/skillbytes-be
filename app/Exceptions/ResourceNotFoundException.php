<?php

namespace App\Exceptions;

/**
 * Exception thrown when a resource is not found
 */
class ResourceNotFoundException extends CustomException
{
    public function __construct(string $resource = "Resource")
    {
        parent::__construct(
            "$resource not found",
            404,
            "RESOURCE_NOT_FOUND"
        );
    }
}

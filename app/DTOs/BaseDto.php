<?php

namespace App\DTOs;

/**
 * Base Data Transfer Object (DTO)
 *
 * Provides common functionality for DTOs
 */
abstract class BaseDto
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}

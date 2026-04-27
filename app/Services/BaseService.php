<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * Base Service class for all services
 *
 * Provides common functionality for service layer
 */
abstract class BaseService
{
    /**
     * Transform data or collection
     */
    protected function transformData($data, $transformer = null)
    {
        if (is_callable($transformer)) {
            return is_array($data) || $data instanceof Collection
                ? collect($data)->map($transformer)
                : $transformer($data);
        }

        return $data;
    }

    /**
     * Handle errors gracefully
     */
    protected function handleError(\Exception $e)
    {
        \Log::error('Service Error: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        throw $e;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class DocumentationController extends Controller
{
    /**
     * Serve OpenAPI specification
     */
    public function openapi()
    {
        $specPath = storage_path('api-docs/openapi.yaml');

        if (!file_exists($specPath)) {
            return response()->json([
                'success' => false,
                'message' => 'API specification not found'
            ], 404);
        }

        return response()->file($specPath, [
            'Content-Type' => 'application/yaml',
        ]);
    }

    /**
     * Serve Swagger UI
     */
    public function swagger()
    {
        return view('swagger');
    }
}

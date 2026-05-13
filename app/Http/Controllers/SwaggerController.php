<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Skillbytes API - PBL Module",
 *     description="REST API for Problem-Based Learning (PBL) Case Management System",
 *     @OA\Contact(
 *         email="support@skillbytes.com"
 *     ),
 *     @OA\License(
 *         name="MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local Development"
 * )
 *
 * @OA\Server(
 *     url="http://145.79.13.180/api",
 *     description="Production Server"
 * )
 *
 * @OA\Components(
 *     @OA\SecurityScheme(
 *         type="http",
 *         scheme="bearer",
 *         bearerFormat="JWT",
 *         securityScheme="bearerAuth",
 *         description="Bearer token for API authentication"
 *     )
 * )
 */

class SwaggerController
{
    // This file is only for Swagger documentation
}

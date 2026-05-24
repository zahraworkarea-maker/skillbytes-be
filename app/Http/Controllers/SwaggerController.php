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
 *     ),
 *     @OA\Schema(
 *         schema="CaseSubmission",
 *         type="object",
 *         description="PBL Case Submission resource",
 *         @OA\Property(property="id", type="integer", example=1, description="Submission ID"),
 *         @OA\Property(property="case_id", type="integer", example=1, description="PBL Case ID"),
 *         @OA\Property(property="user_id", type="integer", example=1, description="Student User ID"),
 *         @OA\Property(property="answer", type="string", nullable=true, example="This is my answer to the case...", description="Student's text answer"),
 *         @OA\Property(property="submission_file", type="string", nullable=true, format="uri", example="http://localhost:8000/storage/submissions/pbl/file.pdf", description="URL to the submission file"),
 *         @OA\Property(property="submission_file_path", type="string", nullable=true, example="submissions/pbl/1716562200_1_abc123.pdf", description="Relative path to submission file"),
 *         @OA\Property(property="submitted_at", type="string", format="date-time", example="2026-05-24T10:30:00Z", description="Submission timestamp"),
 *         @OA\Property(property="score", type="number", format="float", nullable=true, example=85.50, description="Score given by teacher (0-100)"),
 *         @OA\Property(property="feedback", type="string", nullable=true, example="Good work, but needs more detail", description="Feedback from teacher"),
 *         @OA\Property(property="created_at", type="string", format="date-time", example="2026-05-24T10:30:00Z", description="Record creation timestamp"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", example="2026-05-24T11:45:00Z", description="Record last update timestamp"),
 *     )
 * )
 */

class SwaggerController
{
    // This file is only for Swagger documentation
}


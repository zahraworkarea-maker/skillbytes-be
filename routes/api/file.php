<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

Route::get('/pdf/{file}', function ($file) {

    // 🔒 VALIDASI FILE NAME
    if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $file)) {
        abort(400, 'Invalid file name');
    }

    $path = storage_path('app/public/lessons/pdfs/' . $file);

    if (!file_exists($path)) {
        return response()->json(['message' => 'File not found'], 404);
    }

    return response()->file($path, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="'.$file.'"',
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
    ]);
});

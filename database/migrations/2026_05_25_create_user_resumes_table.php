<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->string('title')->nullable(); // Resume title/name
            $table->longText('content')->nullable(); // Resume text content
            $table->string('file_url')->nullable(); // Resume file URL (if uploaded as file)
            $table->string('file_type')->nullable(); // File type (pdf, doc, docx, etc)
            $table->longText('description')->nullable(); // Description/notes about the resume
            $table->boolean('is_active')->default(true); // Mark if this is the active/main resume
            $table->timestamps();
            
            // Index for faster queries
            $table->index('user_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_resumes');
    }
};

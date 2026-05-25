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
        Schema::table('user_resumes', function (Blueprint $table) {
            // Drop unnecessary columns
            $table->dropColumn(['title', 'file_url', 'file_type', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_resumes', function (Blueprint $table) {
            // Restore columns if rollback
            $table->string('title')->after('lesson_id')->nullable();
            $table->string('file_url')->after('content')->nullable();
            $table->string('file_type')->after('file_url')->nullable();
            $table->text('description')->after('file_type')->nullable();
        });
    }
};

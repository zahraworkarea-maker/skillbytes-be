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
            // Add lesson_id to link resume to specific lesson
            $table->foreignId('lesson_id')
                ->nullable()
                ->constrained('lessons')
                ->onDelete('cascade');
            
            // Drop is_active column (not needed anymore)
            $table->dropColumn('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_resumes', function (Blueprint $table) {
            $table->dropForeignIdFor('Lesson');
            $table->dropColumn('lesson_id');
            $table->boolean('is_active')->default(true);
        });
    }
};

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
        Schema::table('questions', function (Blueprint $table) {
            $table->decimal('difficulty_level', 5, 4)->default(0.5000)->comment('IRT Difficulty Parameter (b)');
            $table->decimal('discrimination', 5, 4)->default(1.0000)->comment('IRT Discrimination Parameter (a)');
            $table->decimal('guess_probability', 5, 4)->default(0.2000)->comment('IRT Guessing Parameter (c)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['difficulty_level', 'discrimination', 'guess_probability']);
        });
    }
};

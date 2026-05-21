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
        Schema::create('assessment_levels', function (Blueprint $table) {
            $table->comment('Stores difficulty/proficiency levels for assessments. Each level represents a distinct tier of assessment complexity or learner competency.');
            $table->id();
            $table->unsignedTinyInteger('level')->unique()->comment('Numeric level (e.g., 1,2,3,...)');
            $table->text('description')->nullable()->comment('Detailed description of what this assessment level entails');
            $table->timestamps();

            $table->index('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_levels');
    }
};

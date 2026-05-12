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
        Schema::create('pbl_cases', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->integer('case_number')->unique();
            $table->string('title');
            $table->foreignId('level_id')->constrained('pbl_levels')->onDelete('cascade');
            $table->longText('description');
            $table->string('image_url')->nullable();
            $table->integer('time_limit')->default(0); // dalam menit
            $table->dateTime('start_date');
            $table->dateTime('deadline');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pbl_cases');
    }
};

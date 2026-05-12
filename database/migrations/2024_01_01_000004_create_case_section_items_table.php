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
        Schema::create('case_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('case_sections')->onDelete('cascade');
            $table->enum('type', ['heading', 'text', 'list', 'image']);
            $table->longText('content')->nullable();
            $table->string('image_url')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_section_items');
    }
};

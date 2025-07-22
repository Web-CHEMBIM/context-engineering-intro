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
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Grade 10A", "Class 5B"
            $table->string('grade_level'); // e.g., "10", "5"
            $table->string('section'); // e.g., "A", "B"
            $table->integer('capacity')->default(30);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['grade_level', 'section', 'academic_year_id']);
            $table->index(['is_active', 'academic_year_id']);
            
            // Unique constraint to prevent duplicate class-section combinations per academic year
            $table->unique(['grade_level', 'section', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};
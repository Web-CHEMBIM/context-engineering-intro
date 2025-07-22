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
        Schema::create('class_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->boolean('is_mandatory')->default(true); // Required or elective
            $table->integer('weekly_periods')->default(3); // Number of periods per week
            $table->decimal('passing_marks', 5, 2)->default(40); // Minimum passing marks
            $table->decimal('full_marks', 5, 2)->default(100); // Maximum marks
            $table->text('syllabus')->nullable(); // Curriculum details
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // Indexes and constraints
            $table->unique(['school_class_id', 'subject_id', 'academic_year_id'], 'unique_class_subject_year');
            $table->index(['academic_year_id', 'is_mandatory']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_subject');
    }
};
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
        Schema::create('teacher_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->date('assigned_date');
            $table->enum('status', ['assigned', 'completed', 'unassigned'])->default('assigned');
            $table->boolean('is_primary_teacher')->default(false); // Main teacher for this subject
            $table->integer('weekly_periods')->default(0); // Number of periods per week
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // Indexes and constraints
            $table->unique(['teacher_id', 'subject_id', 'academic_year_id'], 'unique_teacher_subject_year');
            $table->index(['academic_year_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_subject');
    }
};
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
        Schema::create('teacher_class', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->date('assigned_date');
            $table->enum('status', ['assigned', 'completed', 'unassigned'])->default('assigned');
            $table->boolean('is_class_teacher')->default(false); // Main/Homeroom teacher
            $table->text('responsibilities')->nullable(); // JSON or text of specific responsibilities
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // Indexes and constraints
            $table->unique(['teacher_id', 'school_class_id', 'academic_year_id'], 'unique_teacher_class_year');
            $table->index(['academic_year_id', 'status']);
            $table->index(['is_class_teacher', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_class');
    }
};
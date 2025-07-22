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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('student_id')->unique(); // e.g., "STU202400001"
            $table->string('roll_number')->nullable(); // Class roll number
            $table->date('admission_date');
            $table->string('admission_number')->unique();
            $table->foreignId('school_class_id')->constrained('school_classes');
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->enum('student_status', ['enrolled', 'transferred', 'graduated', 'dropped'])->default('enrolled');
            $table->string('blood_group')->nullable();
            $table->text('medical_conditions')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->decimal('total_fees', 10, 2)->default(0);
            $table->decimal('fees_paid', 10, 2)->default(0);
            $table->decimal('fees_pending', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['student_status', 'academic_year_id']);
            $table->index(['school_class_id', 'academic_year_id']);
            $table->index(['admission_date']);
            $table->unique(['roll_number', 'school_class_id'], 'unique_roll_per_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
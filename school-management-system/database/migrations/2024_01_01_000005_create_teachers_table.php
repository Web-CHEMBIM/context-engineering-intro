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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('teacher_id')->unique(); // e.g., "TCH202400001"
            $table->string('employee_id')->unique();
            $table->date('hire_date');
            $table->string('department')->nullable(); // e.g., "Science", "Mathematics"
            $table->enum('employment_type', ['full-time', 'part-time', 'contract'])->default('full-time');
            $table->enum('teacher_status', ['active', 'inactive', 'on-leave', 'terminated'])->default('active');
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('qualification')->nullable(); // e.g., "Masters in Mathematics"
            $table->integer('experience_years')->default(0);
            $table->text('specializations')->nullable(); // JSON or comma-separated
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->date('contract_end_date')->nullable(); // For contract employees
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['teacher_status', 'employment_type']);
            $table->index(['department']);
            $table->index(['hire_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
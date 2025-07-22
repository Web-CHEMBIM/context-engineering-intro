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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Mathematics", "English Literature"
            $table->string('code')->unique(); // e.g., "MATH101", "ENG102"
            $table->text('description')->nullable();
            $table->integer('credit_hours')->default(3);
            $table->string('department')->nullable(); // e.g., "Science", "Arts"
            $table->boolean('is_core_subject')->default(false); // Core vs Elective
            $table->boolean('is_active')->default(true);
            $table->json('grade_levels')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['is_active', 'is_core_subject']);
            $table->index('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
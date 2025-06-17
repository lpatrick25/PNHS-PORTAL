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
        Schema::create('teacher_subject_loads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('restrict');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('restrict');
            $table->foreignId('school_year_id')->constrained('school_years')->onDelete('restrict');
            $table->enum('grade_level', [7,8,9,10,11,12]);
            $table->string('section');
            $table->timestamps();

            $table->unique(['subject_id', 'grade_level', 'section', 'school_year_id'], 'teacher_subject_loads_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_subject_loads');
    }
};

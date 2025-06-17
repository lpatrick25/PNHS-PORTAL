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
        Schema::create('student_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('restrict');
            $table->foreignId('adviser_id')->constrained('advisers')->onDelete('restrict');
            $table->enum('grade_level', [7,8,9,10,11,12]);
            $table->foreignId('school_year_id')->constrained('school_years')->onDelete('restrict');
            $table->string('section', 20);
            $table->enum('status', ['ENROLLED', 'DROPPED'])->default('ENROLLED');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_statuses');
    }
};

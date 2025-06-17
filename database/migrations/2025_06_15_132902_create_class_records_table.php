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
        Schema::create('class_records', function (Blueprint $table) {
            $table->id();
            $table->string('records_name', 50);
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('teacher_subject_load_id')->constrained('teacher_subject_loads')->onDelete('cascade');
            $table->foreignId('school_year_id')->constrained('school_years')->onDelete('cascade');
            $table->integer('total_score')->nullable();
            $table->integer('student_score')->default(0);
            $table->enum('records_type', ['Written Works', 'Performance Tasks', 'Quarterly Assessment']);
            $table->enum('quarter', ['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'])->default('1st Quarter');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_records');
    }
};

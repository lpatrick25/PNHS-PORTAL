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
            $table->string('student_lrn')->unique();
            $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('rfid_no')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('extension_name')->nullable();
            $table->bigInteger('province_code')->unsigned();
            $table->foreign('province_code')->references('province_code')->on('provinces')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('municipality_code')->unsigned();
            $table->foreign('municipality_code')->references('municipality_code')->on('municipalities')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('barangay_code')->unsigned();
            $table->foreign('barangay_code')->references('barangay_code')->on('barangays')->onUpdate('cascade')->onDelete('cascade');
            $table->string('zip_code', 10);
            $table->string('religion', 50);
            $table->date('birthday');
            $table->enum('sex', ['Male', 'Female']);
            $table->string('disability', 50)->default('None');
            $table->string('email', 50)->unique();
            $table->string('parent_contact', 20);
            $table->string('contact', 20);
            $table->bigInteger('present_province_code')->unsigned();
            $table->foreign('present_province_code')->references('province_code')->on('provinces')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('present_municipality_code')->unsigned();
            $table->foreign('present_municipality_code')->references('municipality_code')->on('municipalities')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('present_barangay_code')->unsigned();
            $table->foreign('present_barangay_code')->references('barangay_code')->on('barangays')->onUpdate('cascade')->onDelete('cascade');
            $table->string('present_zip_code', 10);
            $table->string('mother_first_name');
            $table->string('mother_middle_name')->nullable();
            $table->string('mother_last_name');
            $table->string('mother_address');
            $table->string('father_first_name');
            $table->string('father_middle_name')->nullable();
            $table->string('father_last_name');
            $table->string('father_suffix')->nullable();
            $table->string('father_address');
            $table->string('guardian')->nullable();
            $table->string('guardian_address')->nullable();
            $table->timestamps();
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

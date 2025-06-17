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
        Schema::create('principals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
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
            $table->string('civil_status', 15);
            $table->string('email', 50);
            $table->string('contact', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('principals');
    }
};

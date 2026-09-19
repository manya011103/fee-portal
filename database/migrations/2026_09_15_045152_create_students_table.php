<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('students', function (Blueprint $table) {
        $table->id();
        $table->date('registration_date')->nullable();
        $table->string('enrollment_no')->unique();
        $table->string('name');
        $table->string('father_name')->nullable();
        $table->string('mother_name')->nullable();
        $table->string('mobile')->unique();
        $table->string('father_mobile')->nullable();
        $table->string('mother_mobile')->nullable();
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
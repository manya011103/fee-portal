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
        Schema::create('fee_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');

            $table->string('class_name');
            $table->decimal('total_fee', 10, 2)->default(0);
            $table->decimal('scholarship_fee', 10, 2)->default(0);
            $table->boolean('is_fully_paid')->default(false);

            $table->timestamps();

            // Ek student ki ek class ki sirf ek hi entry ho — duplicate import pe update hoga
            $table->unique(['student_id', 'class_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_records');
    }
};
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

        $table->date('fee1_paid_date')->nullable();
        $table->decimal('fee1_paid_amt', 10, 2)->nullable();
        $table->date('fee2_paid_date')->nullable();
        $table->decimal('fee2_paid_amt', 10, 2)->nullable();
        $table->date('fee3_paid_date')->nullable();
        $table->decimal('fee3_paid_amt', 10, 2)->nullable();
        $table->date('fee4_paid_date')->nullable();
        $table->decimal('fee4_paid_amt', 10, 2)->nullable();
        $table->date('fee5_paid_date')->nullable();
        $table->decimal('fee5_paid_amt', 10, 2)->nullable();
        $table->date('fee6_paid_date')->nullable();
        $table->decimal('fee6_paid_amt', 10, 2)->nullable();

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

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
    Schema::create('fee_payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('fee_record_id')->constrained()->onDelete('cascade');
        $table->decimal('amount', 10, 2);
        $table->date('payment_date');
        $table->enum('payment_mode', ['accounts', 'portal'])->default('accounts');
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('fee_payments');
}
};

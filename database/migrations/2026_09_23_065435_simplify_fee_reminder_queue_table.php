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
    Schema::dropIfExists('fee_reminder_queue');

    Schema::create('fee_reminder_queue', function (Blueprint $table) {
        $table->id();
        $table->string('mobile');
    });
}

public function down(): void
{
    Schema::dropIfExists('fee_reminder_queue');
}
};

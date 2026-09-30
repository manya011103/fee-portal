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
    Schema::table('fee_payments', function (Blueprint $table) {
        $table->decimal('fine_portion', 10, 2)->default(0)->after('amount');
        $table->decimal('scholarship_lapse_portion', 10, 2)->default(0)->after('fine_portion');
    });
}

public function down(): void
{
    Schema::table('fee_payments', function (Blueprint $table) {
        $table->dropColumn(['fine_portion', 'scholarship_lapse_portion']);
    });
}
};

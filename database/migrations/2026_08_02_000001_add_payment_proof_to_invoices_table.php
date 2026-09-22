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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('payment_proof')->nullable()->after('status');
            $table->text('payment_note')->nullable()->after('payment_proof');
            $table->timestamp('verified_at')->nullable()->after('payment_note');
            $table->unsignedBigInteger('verified_by')->nullable()->after('verified_at');

            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['payment_proof', 'payment_note', 'verified_at', 'verified_by']);
        });
    }
};

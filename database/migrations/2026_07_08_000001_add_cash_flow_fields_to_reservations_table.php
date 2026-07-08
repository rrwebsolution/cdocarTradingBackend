<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('financing_record_id')->nullable()->after('vehicle_id')->constrained()->nullOnDelete();
            $table->string('proof_of_payment_url')->nullable()->after('payment_method');
            $table->string('payment_reference_number')->nullable()->after('proof_of_payment_url');
            $table->string('verified_by')->nullable()->after('status');
            $table->date('verified_at')->nullable()->after('verified_by');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('financing_record_id');
            $table->dropColumn(['proof_of_payment_url', 'payment_reference_number', 'verified_by', 'verified_at']);
        });
    }
};

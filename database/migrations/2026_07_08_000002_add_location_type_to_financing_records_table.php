<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financing_records', function (Blueprint $table) {
            $table->string('customer_location_type')->nullable()->after('customer_id');
            $table->date('requirements_submitted_at')->nullable()->after('documents');
        });
    }

    public function down(): void
    {
        Schema::table('financing_records', function (Blueprint $table) {
            $table->dropColumn(['customer_location_type', 'requirements_submitted_at']);
        });
    }
};

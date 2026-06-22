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
        Schema::create('financing_records', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('financing_company');
            $table->string('application_number')->nullable();
            $table->decimal('approved_amount', 12, 2)->default(0);
            $table->decimal('down_payment', 12, 2)->default(0);
            $table->date('approved_at')->nullable();
            $table->json('documents')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financing_records');
    }
};

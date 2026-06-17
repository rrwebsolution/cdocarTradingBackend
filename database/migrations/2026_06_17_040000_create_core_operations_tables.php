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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('color')->nullable();
            $table->string('engine_number')->nullable()->unique();
            $table->string('chassis_number')->nullable()->unique();
            $table->string('plate_number')->nullable()->unique();
            $table->unsignedInteger('mileage')->default(0);
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->string('location')->nullable();
            $table->string('condition')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('status')->default('available');
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('contact')->nullable();
            $table->text('address')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('contact')->nullable();
            $table->string('position');
            $table->string('schedule')->nullable();
            $table->string('activity')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('reserved_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('status')->default('for approval');
            $table->timestamps();
        });

        Schema::create('sales_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_method')->default('cash');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->json('financing_details')->nullable();
            $table->string('status')->default('pending');
            $table->date('sold_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('sales_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('method')->default('cash');
            $table->string('proof_url')->nullable();
            $table->string('status')->default('paid');
            $table->date('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('service_type');
            $table->text('issue')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('progress')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('job_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('service_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->string('activity');
            $table->string('repair_status')->nullable();
            $table->string('washing_status')->nullable();
            $table->text('maintenance_record')->nullable();
            $table->date('scheduled_at')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('deed_of_sales', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('sales_transaction_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('generated_at');
            $table->json('document_data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deed_of_sales');
        Schema::dropIfExists('job_orders');
        Schema::dropIfExists('service_requests');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('sales_transactions');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('staff');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('vehicles');
    }
};

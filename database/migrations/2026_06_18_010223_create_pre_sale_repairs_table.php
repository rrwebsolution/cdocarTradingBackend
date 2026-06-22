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
        Schema::create('pre_sale_repairs', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->text('issue');
            $table->string('affected_part')->nullable();
            $table->text('action_taken')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->date('inspected_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->string('before_photo_url')->nullable();
            $table->string('after_photo_url')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('for inspection');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_sale_repairs');
    }
};

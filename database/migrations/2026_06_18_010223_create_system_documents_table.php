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
        Schema::create('system_documents', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->nullableMorphs('documentable');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('type');
            $table->string('owner_name')->nullable();
            $table->string('file_url')->nullable();
            $table->date('uploaded_at')->nullable();
            $table->string('verified_by')->nullable();
            $table->date('verified_at')->nullable();
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
        Schema::dropIfExists('system_documents');
    }
};

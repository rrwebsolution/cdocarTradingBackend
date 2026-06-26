<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('stock_no')->nullable()->unique()->after('name');
            $table->string('variant')->nullable()->after('year');
            $table->string('transmission')->nullable()->after('color');
            $table->string('fuel_type')->nullable()->after('transmission');
            $table->decimal('reservation_fee', 12, 2)->nullable()->after('selling_price');
            $table->string('or_cr_number')->nullable()->after('condition');
            $table->date('registration_expiry')->nullable()->after('or_cr_number');
            $table->string('insurance')->nullable()->after('registration_expiry');
            $table->json('interior_photo_urls')->nullable()->after('photo_url');
            $table->json('exterior_photo_urls')->nullable()->after('interior_photo_urls');
            $table->text('description')->nullable()->after('exterior_photo_urls');
            $table->text('features')->nullable()->after('description');
            $table->text('remarks')->nullable()->after('features');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropUnique(['stock_no']);
            $table->dropColumn([
                'stock_no',
                'variant',
                'transmission',
                'fuel_type',
                'reservation_fee',
                'or_cr_number',
                'registration_expiry',
                'insurance',
                'interior_photo_urls',
                'exterior_photo_urls',
                'description',
                'features',
                'remarks',
            ]);
        });
    }
};

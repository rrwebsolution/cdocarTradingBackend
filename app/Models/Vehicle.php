<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'stock_no',
        'brand',
        'model',
        'year',
        'variant',
        'color',
        'transmission',
        'fuel_type',
        'engine_number',
        'chassis_number',
        'plate_number',
        'mileage',
        'purchase_price',
        'selling_price',
        'reservation_fee',
        'location',
        'condition',
        'or_cr_number',
        'registration_expiry',
        'insurance',
        'photo_url',
        'interior_photo_urls',
        'exterior_photo_urls',
        'description',
        'features',
        'remarks',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'reservation_fee' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'interior_photo_urls' => 'array',
            'exterior_photo_urls' => 'array',
            'registration_expiry' => 'date',
            'year' => 'integer',
        ];
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function salesTransactions(): HasMany
    {
        return $this->hasMany(SalesTransaction::class);
    }
}

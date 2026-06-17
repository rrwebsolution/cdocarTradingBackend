<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SalesTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'customer_id',
        'vehicle_id',
        'reservation_id',
        'payment_method',
        'total_amount',
        'paid_amount',
        'balance',
        'financing_details',
        'status',
        'sold_at',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'financing_details' => 'array',
            'paid_amount' => 'decimal:2',
            'sold_at' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function deedOfSale(): HasOne
    {
        return $this->hasOne(DeedOfSale::class);
    }
}

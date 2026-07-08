<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'customer_id',
        'vehicle_id',
        'financing_record_id',
        'amount',
        'payment_method',
        'proof_of_payment_url',
        'payment_reference_number',
        'reserved_at',
        'expires_at',
        'status',
        'remarks',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expires_at' => 'date',
            'reserved_at' => 'date',
            'verified_at' => 'date',
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

    public function financingRecord(): BelongsTo
    {
        return $this->belongsTo(FinancingRecord::class);
    }
}

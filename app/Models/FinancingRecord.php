<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FinancingRecord extends Model
{
    protected $fillable = [
        'reference',
        'customer_id',
        'customer_location_type',
        'vehicle_id',
        'sales_transaction_id',
        'financing_company',
        'application_number',
        'approved_amount',
        'down_payment',
        'approved_at',
        'documents',
        'requirements_submitted_at',
        'remarks',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'approved_amount' => 'decimal:2',
            'approved_at' => 'date',
            'documents' => 'array',
            'down_payment' => 'decimal:2',
            'requirements_submitted_at' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesTransaction(): BelongsTo
    {
        return $this->belongsTo(SalesTransaction::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function reservation(): HasOne
    {
        return $this->hasOne(Reservation::class);
    }
}

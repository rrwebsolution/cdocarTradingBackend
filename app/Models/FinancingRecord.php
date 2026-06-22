<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancingRecord extends Model
{
    protected $fillable = [
        'reference',
        'customer_id',
        'vehicle_id',
        'sales_transaction_id',
        'financing_company',
        'application_number',
        'approved_amount',
        'down_payment',
        'approved_at',
        'documents',
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
}

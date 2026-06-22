<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleRelease extends Model
{
    protected $fillable = [
        'reference',
        'customer_id',
        'vehicle_id',
        'sales_transaction_id',
        'checklist_status',
        'document_status',
        'checklist',
        'released_at',
        'released_by',
        'remarks',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'checklist' => 'array',
            'released_at' => 'date',
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

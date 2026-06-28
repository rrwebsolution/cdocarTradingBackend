<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'customer_id',
        'vehicle_id',
        'service_type',
        'issue',
        'photo_url',
        'preferred_service_date',
        'progress',
        'status',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function jobOrder(): HasOne
    {
        return $this->hasOne(JobOrder::class);
    }
}

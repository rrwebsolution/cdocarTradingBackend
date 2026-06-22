<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreSaleRepair extends Model
{
    protected $fillable = [
        'reference',
        'vehicle_id',
        'assigned_staff_id',
        'issue',
        'affected_part',
        'action_taken',
        'cost',
        'inspected_at',
        'completed_at',
        'before_photo_url',
        'after_photo_url',
        'remarks',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'date',
            'cost' => 'decimal:2',
            'inspected_at' => 'date',
        ];
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'assigned_staff_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}

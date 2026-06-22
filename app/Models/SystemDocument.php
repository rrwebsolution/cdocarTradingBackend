<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SystemDocument extends Model
{
    protected $fillable = [
        'reference',
        'documentable_type',
        'documentable_id',
        'customer_id',
        'title',
        'type',
        'owner_name',
        'file_url',
        'uploaded_at',
        'verified_by',
        'verified_at',
        'remarks',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'date',
            'verified_at' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }
}

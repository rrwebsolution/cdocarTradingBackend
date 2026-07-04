<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'sales_transaction_id',
        'generated_at',
        'document_data',
    ];

    protected function casts(): array
    {
        return [
            'document_data' => 'array',
            'generated_at' => 'date',
        ];
    }

    public function salesTransaction(): BelongsTo
    {
        return $this->belongsTo(SalesTransaction::class);
    }
}

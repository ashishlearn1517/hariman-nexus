<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    public const TYPE_SERVICE = 'service';
    public const TYPE_PRODUCT = 'product';

    protected $fillable = [
        'quotation_id',
        'item_type',
        'item_source_id',
        'item_name',
        'quantity',
        'rate',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'rate' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }
}

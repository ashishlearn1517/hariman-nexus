<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoicePayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'payment_date',
        'amount',
        'payment_method',
        'receipt_number',
        'reference',
        'receipt_path',
        'receipt_web_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return array<string, string>
     */
    public static function methodOptions(): array
    {
        return [
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            'card' => 'Card',
            'upi' => 'UPI',
            'cheque' => 'Cheque',
            'gateway' => 'Payment Gateway',
            'other' => 'Other',
        ];
    }
}

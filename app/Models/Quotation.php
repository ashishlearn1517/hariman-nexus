<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CONVERTED = 'converted';

    protected $fillable = [
        'quotation_no',
        'sequence_no',
        'client_id',
        'project_id',
        'currency_id',
        'tax_setting_id',
        'term_condition_id',
        'quotation_date',
        'validity_date',
        'subtotal',
        'tax_rate_percent',
        'tax_amount',
        'total',
        'status',
        'sent_at',
        'approved_at',
        'rejected_at',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'quotation_date' => 'date',
            'validity_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_rate_percent' => 'decimal:4',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'sent_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function taxSetting(): BelongsTo
    {
        return $this->belongsTo(TaxSetting::class);
    }

    public function termCondition(): BelongsTo
    {
        return $this->belongsTo(TermCondition::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'source_quotation_id');
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SENT => 'Sent',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_CONVERTED => 'Converted',
        ];
    }
}

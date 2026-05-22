<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    public const TYPE_LOCAL = 'local';
    public const TYPE_ABROAD = 'abroad';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'client_code',
        'sequence_no',
        'project_id',
        'name',
        'client_type',
        'email',
        'phone',
        'address',
        'tax_applicable',
        'tax_percent',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tax_applicable' => 'boolean',
            'tax_percent' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return [
            self::TYPE_LOCAL => 'Local',
            self::TYPE_ABROAD => 'Abroad',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }
}

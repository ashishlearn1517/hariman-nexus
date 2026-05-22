<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TermCondition extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $table = 'terms_conditions';

    protected $fillable = [
        'name',
        'content',
        'status',
    ];

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

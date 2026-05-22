<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NumberingSetting extends Model
{
    public const SINGLETON_KEY = 'default';

    protected $fillable = [
        'singleton_key',
        'separator',
        'padding',
        'include_year_for_clients',
        'include_year_for_invoices',
        'include_year_for_quotations',
        'local_client_prefix',
        'abroad_client_prefix',
        'product_prefix',
        'invoice_prefix',
        'quotation_prefix',
        'next_local_client_number',
        'next_abroad_client_number',
        'next_product_number',
        'next_invoice_number',
        'next_quotation_number',
    ];

    protected function casts(): array
    {
        return [
            'padding' => 'integer',
            'include_year_for_clients' => 'boolean',
            'include_year_for_invoices' => 'boolean',
            'include_year_for_quotations' => 'boolean',
            'next_local_client_number' => 'integer',
            'next_abroad_client_number' => 'integer',
            'next_product_number' => 'integer',
            'next_invoice_number' => 'integer',
            'next_quotation_number' => 'integer',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrNew(
            ['singleton_key' => self::SINGLETON_KEY],
            self::defaults(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'singleton_key' => self::SINGLETON_KEY,
            'separator' => '-',
            'padding' => 4,
            'include_year_for_clients' => true,
            'include_year_for_invoices' => true,
            'include_year_for_quotations' => true,
            'local_client_prefix' => 'LC',
            'abroad_client_prefix' => 'AC',
            'product_prefix' => 'PROD',
            'invoice_prefix' => 'INV',
            'quotation_prefix' => 'QUO',
            'next_local_client_number' => 1,
            'next_abroad_client_number' => 1,
            'next_product_number' => 1,
            'next_invoice_number' => 1,
            'next_quotation_number' => 1,
        ];
    }

    public function preview(string $prefix, int $nextNumber, bool $includeYear = false): string
    {
        $parts = [$prefix];

        if ($includeYear) {
            $parts[] = (string) now()->year;
        }

        $parts[] = str_pad((string) $nextNumber, $this->padding, '0', STR_PAD_LEFT);

        return implode($this->separator, $parts);
    }
}

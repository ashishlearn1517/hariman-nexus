<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    public const SINGLETON_KEY = 'default';

    protected $fillable = [
        'singleton_key',
        'company_name',
        'company_email',
        'company_phone_country',
        'company_phone_code',
        'company_phone_local',
        'company_location_country',
        'company_location',
        'website',
        'tax_registration_number',
        'company_logo_path',
        'company_logo_web_path',
        'payment_label',
        'payment_reference',
        'bank_details',
        'payment_qr_path',
        'payment_qr_web_path',
    ];

    public static function current(): self
    {
        return self::query()->firstOrNew(
            ['singleton_key' => self::SINGLETON_KEY],
            [
                'company_name' => config('app.name', 'Hariman Nexus'),
                'company_logo_web_path' => 'assets/images/hariman-nexus-wordmark.png',
                'payment_label' => 'Payment Instructions',
            ],
        );
    }

    /**
     * @return array<string, array{label: string, code: string}>
     */
    public static function countryOptions(): array
    {
        return config('countries', []);
    }

    public function phone(): string
    {
        return trim(($this->company_phone_code ?? '').' '.($this->company_phone_local ?? ''));
    }
}

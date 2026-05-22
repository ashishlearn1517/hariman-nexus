<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSetting extends Model
{
    public const SINGLETON_KEY = 'default';

    protected $fillable = [
        'singleton_key',
        'mail_host',
        'mail_port',
        'mail_encryption',
        'mail_username',
        'mail_password',
        'mail_from_address',
        'mail_from_name',
        'mail_cc_address',
        'test_email_recipient',
        'invoice_email_subject',
        'invoice_email_body',
        'reminder_email_subject',
        'reminder_email_body',
        'overdue_email_subject',
        'overdue_email_body',
        'quotation_email_subject',
        'quotation_email_body',
    ];

    protected function casts(): array
    {
        return [
            'mail_port' => 'integer',
            'mail_password' => 'encrypted',
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
     * @return array<string, string|int>
     */
    public static function defaults(): array
    {
        return [
            'singleton_key' => self::SINGLETON_KEY,
            'mail_host' => '',
            'mail_port' => 465,
            'mail_encryption' => 'ssl',
            'mail_username' => '',
            'mail_from_address' => '',
            'mail_from_name' => config('app.name', 'Hariman Nexus'),
            'mail_cc_address' => '',
            'test_email_recipient' => '',
            'invoice_email_subject' => 'Invoice {invoice_no}',
            'invoice_email_body' => "Dear {client_name},\n\nPlease find attached your invoice {invoice_no}.\n\nProject: {project_name}\nInvoice Date: {invoice_date}\nDue Date: {due_date}\nTotal Amount: {total_amount}\nBalance Due: {balance_due}\n\n{payment_instructions}\n\nThank you for your business.\n\nRegards,\n{company_name}",
            'reminder_email_subject' => 'Payment Reminder for Invoice {invoice_no}',
            'reminder_email_body' => "Dear {client_name},\n\nThis is a friendly reminder for invoice {invoice_no}, due on {due_date}.\n\nProject: {project_name}\nInvoice Date: {invoice_date}\nTotal Amount: {total_amount}\nBalance Due: {balance_due}\n\n{payment_instructions}\n\nPlease let us know if you need any clarification on this invoice.\n\nRegards,\n{company_name}",
            'overdue_email_subject' => 'Overdue Notice for Invoice {invoice_no}',
            'overdue_email_body' => "Dear {client_name},\n\nInvoice {invoice_no} is now overdue. Please arrange payment as soon as possible.\n\nProject: {project_name}\nInvoice Date: {invoice_date}\nDue Date: {due_date}\nTotal Amount: {total_amount}\nBalance Due: {balance_due}\n\n{payment_instructions}\n\nIf payment has already been made, please reply with the payment reference so we can update our records.\n\nRegards,\n{company_name}",
            'quotation_email_subject' => 'Quotation {quotation_no}',
            'quotation_email_body' => "Dear {client_name},\n\nPlease find attached quotation {quotation_no}.\n\nProject: {project_name}\nQuotation Date: {quotation_date}\nValid Until: {validity_date}\nTotal Amount: {total_amount}\n\nPlease review the quotation and let us know if you would like us to proceed.\n\nRegards,\n{company_name}",
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function encryptionOptions(): array
    {
        return [
            'ssl' => 'SSL',
            'tls' => 'TLS',
        ];
    }
}

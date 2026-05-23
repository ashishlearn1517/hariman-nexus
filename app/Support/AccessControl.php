<?php

namespace App\Support;

use App\Models\User;

class AccessControl
{
    public const PERMISSIONS = [
        'view dashboard',
        'view projects',
        'create projects',
        'edit projects',
        'view clients',
        'create clients',
        'edit clients',
        'view products',
        'create products',
        'edit products',
        'view services',
        'create services',
        'edit services',
        'view terms',
        'create terms',
        'edit terms',
        'view quotations',
        'create quotations',
        'edit quotations',
        'delete quotations',
        'approve quotations',
        'send quotations',
        'view invoices',
        'create invoices',
        'edit invoices',
        'delete invoices',
        'send invoices',
        'manage payments',
        'manage settings',
        'manage users',
        'view users',
        'create users',
        'edit users',
        'deactivate users',
        'delete users',
        'view activity logs',
        'view reports',
    ];

    public static function rolePermissions(): array
    {
        $viewOnly = [
            'view dashboard',
            'view projects',
            'view clients',
            'view products',
            'view services',
            'view terms',
            'view quotations',
            'view invoices',
            'view reports',
        ];

        return [
            User::ROLE_SUPER_ADMIN => self::PERMISSIONS,
            User::ROLE_ADMIN => array_values(array_diff(self::PERMISSIONS, [
                'manage settings',
                'manage users',
                'edit users',
                'delete users',
            ])),
            User::ROLE_ACCOUNTANT => [
                'view dashboard',
                'view quotations',
                'view invoices',
                'create invoices',
                'edit invoices',
                'delete invoices',
                'send invoices',
                'manage payments',
                'view reports',
            ],
            User::ROLE_OPERATIONS_STAFF => [
                'view dashboard',
                'view projects',
                'create projects',
                'edit projects',
                'view clients',
                'create clients',
                'edit clients',
                'view products',
                'create products',
                'edit products',
                'view services',
                'create services',
                'edit services',
                'view terms',
                'create terms',
                'edit terms',
                'view quotations',
                'create quotations',
                'edit quotations',
                'delete quotations',
                'approve quotations',
                'send quotations',
            ],
            User::ROLE_VIEWER => $viewOnly,
        ];
    }
}

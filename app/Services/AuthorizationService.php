<?php

namespace App\Services;

use App\Contracts\AuthorizationServiceInterface;

class AuthorizationService implements AuthorizationServiceInterface
{
    public function hasPermission(
        $user,
        string $permission
    ): bool
    {
        $permissions = [

            // Administrador
            1 => [
                'manage_projects',
                'manage_users',
                'manage_banks',
                'manage_accounts',
                'manage_roles',
                'view_reports',
                'manage_rols',
                'manage_classification'
            ],

            // Usuario normal
            2 => [
                //
            ],

            // Auditor ex
            3 => [
                //
                'manage_projects'
            ]
        ];

        return in_array(
            $permission,
            $permissions[$user->id_rol] ?? []
        );
    }
}
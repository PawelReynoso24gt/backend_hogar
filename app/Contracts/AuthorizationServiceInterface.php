<?php

namespace App\Contracts;

interface AuthorizationServiceInterface
{
    public function hasPermission(
        $user,
        string $permission
    ): bool;
}
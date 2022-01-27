<?php

namespace App\Enums;

/**
 * Enum UserRole.
 *
 * @method static UserRole Admin()
 * @method static UserRole Manager()
 */
class UserRole extends Enum
{
    public const Admin = 'admin';
    public const Manager = 'manager';
}

<?php

namespace App\Enums;

/**
 * Enum UserRole.
 *
 * @method static UserRole Admin()
 * @method static UserRole Manager()
 * @method static UserRole Customer()
 *
 * @SuppressWarnings(PHPMD)
 */
class UserRole extends Enum
{
    public const Admin = 'admin';
    public const Manager = 'manager';
    public const Customer = 'customer';
}

<?php

declare(strict_types=1);

namespace Weale\Domain\User\ValueObjects;

enum UserRole: string
{
    case ADMIN    = 'admin';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return match($this) {
            self::ADMIN    => 'Administrator',
            self::CUSTOMER => 'Customer',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }
}

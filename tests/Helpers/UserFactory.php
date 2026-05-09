<?php

declare(strict_types=1);

namespace Weale\Tests\Helpers;

use Weale\Domain\User\User;
use Weale\Domain\User\ValueObjects\Email;
use Weale\Domain\User\ValueObjects\HashedPassword;
use Weale\Domain\User\ValueObjects\UserRole;

final class UserFactory
{
    public static function make(array $overrides = []): User
    {
        $id = uniqid();

        return User::register(
            name:     $overrides['name']     ?? 'User ' . $id,
            email:    new Email($overrides['email'] ?? "user{$id}@example.com"),
            password: HashedPassword::fromPlainText($overrides['password'] ?? 'password123'),
            role:     $overrides['role']     ?? UserRole::CUSTOMER,
        );
    }

    public static function makeAdmin(array $overrides = []): User
    {
        return self::make(array_merge($overrides, ['role' => UserRole::ADMIN]));
    }
}

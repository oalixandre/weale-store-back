<?php

declare(strict_types=1);

namespace Weale\Application\User;

final class RegisterUserCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
    ) {}
}

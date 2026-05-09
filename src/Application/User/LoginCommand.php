<?php

declare(strict_types=1);

namespace Weale\Application\User;

final class LoginCommand
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}
}

<?php

declare(strict_types=1);

namespace Weale\Application\User;

final class LoginResponse
{
    public function __construct(
        public readonly string       $token,
        public readonly int          $expiresIn,
        public readonly UserResponse $user,
    ) {}

    public function toArray(): array
    {
        return [
            'access_token' => $this->token,
            'token_type'   => 'Bearer',
            'expires_in'   => $this->expiresIn,
            'user'         => $this->user->toArray(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Weale\Application\User;

use Weale\Domain\User\User;

final class UserResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $role,
        public readonly bool   $active,
        public readonly string $createdAt,
    ) {}

    public static function fromUser(User $user): self
    {
        return new self(
            id:        $user->id()->value(),
            name:      $user->name(),
            email:     $user->email()->value(),
            role:      $user->role()->value,
            active:    $user->isActive(),
            createdAt: $user->createdAt()->format(DATE_ATOM),
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'role'       => $this->role,
            'active'     => $this->active,
            'created_at' => $this->createdAt,
        ];
    }
}

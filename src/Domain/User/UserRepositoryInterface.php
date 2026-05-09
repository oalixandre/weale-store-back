<?php

declare(strict_types=1);

namespace Weale\Domain\User;

use Weale\Domain\User\ValueObjects\Email;
use Weale\Domain\User\ValueObjects\UserId;

interface UserRepositoryInterface
{
    public function findById(UserId $id): ?User;
    public function findByEmail(Email $email): ?User;
    public function save(User $user): void;
    public function delete(UserId $id): void;
    public function existsByEmail(Email $email): bool;
}

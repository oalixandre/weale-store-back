<?php

declare(strict_types=1);

namespace Weale\Domain\User;

use DateTimeImmutable;
use InvalidArgumentException;
use Weale\Domain\Shared\Events\DomainEventInterface;
use Weale\Domain\User\Events\UserRegisteredEvent;
use Weale\Domain\User\ValueObjects\Email;
use Weale\Domain\User\ValueObjects\HashedPassword;
use Weale\Domain\User\ValueObjects\UserId;
use Weale\Domain\User\ValueObjects\UserRole;

final class User
{
    private UserId $id;
    private string $name;
    private Email $email;
    private HashedPassword $password;
    private UserRole $role;
    private bool $active;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    /** @var DomainEventInterface[] */
    private array $domainEvents = [];

    private function __construct(
        UserId $id,
        string $name,
        Email $email,
        HashedPassword $password,
        UserRole $role,
    ) {
        $this->id        = $id;
        $this->name      = $name;
        $this->email     = $email;
        $this->password  = $password;
        $this->role      = $role;
        $this->active    = true;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function register(
        string $name,
        Email $email,
        HashedPassword $password,
        UserRole $role = UserRole::CUSTOMER,
    ): self {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('User name cannot be empty.');
        }

        $user = new self(UserId::generate(), trim($name), $email, $password, $role);
        $user->recordEvent(new UserRegisteredEvent($user->id, $email));

        return $user;
    }

    public static function reconstitute(
        UserId $id,
        string $name,
        Email $email,
        HashedPassword $password,
        UserRole $role,
        bool $active,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        $user              = new self($id, $name, $email, $password, $role);
        $user->active      = $active;
        $user->createdAt   = $createdAt;
        $user->updatedAt   = $updatedAt;
        $user->domainEvents = [];
        return $user;
    }

    public function changeName(string $name): void
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('User name cannot be empty.');
        }
        $this->name      = trim($name);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changePassword(HashedPassword $password): void
    {
        $this->password  = $password;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->active    = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->active    = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function verifyPassword(string $plainText): bool
    {
        return $this->password->verify($plainText);
    }

    // Getters
    public function id(): UserId                  { return $this->id; }
    public function name(): string                { return $this->name; }
    public function email(): Email                { return $this->email; }
    public function password(): HashedPassword    { return $this->password; }
    public function role(): UserRole              { return $this->role; }
    public function isActive(): bool              { return $this->active; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }

    private function recordEvent(DomainEventInterface $event): void
    {
        $this->domainEvents[] = $event;
    }

    /** @return DomainEventInterface[] */
    public function pullDomainEvents(): array
    {
        $events             = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }
}

<?php

declare(strict_types=1);

namespace Weale\Domain\User\Events;

use DateTimeImmutable;
use Weale\Domain\Shared\Events\DomainEventInterface;
use Weale\Domain\User\ValueObjects\Email;
use Weale\Domain\User\ValueObjects\UserId;

final class UserRegisteredEvent implements DomainEventInterface
{
    private DateTimeImmutable $occurredOn;

    public function __construct(
        private readonly UserId $userId,
        private readonly Email  $email,
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }

    public function occurredOn(): DateTimeImmutable { return $this->occurredOn; }
    public function eventName(): string             { return 'user.registered'; }
    public function userId(): UserId                { return $this->userId; }
    public function email(): Email                  { return $this->email; }
}

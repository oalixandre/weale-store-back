<?php

declare(strict_types=1);

namespace Weale\Domain\Shared\Events;

use DateTimeImmutable;

interface DomainEventInterface
{
    public function occurredOn(): DateTimeImmutable;
    public function eventName(): string;
}

<?php

declare(strict_types=1);

namespace Weale\Domain\Order\Events;

use DateTimeImmutable;
use Weale\Domain\Order\ValueObjects\OrderId;
use Weale\Domain\Product\ValueObjects\Money;
use Weale\Domain\Shared\Events\DomainEventInterface;
use Weale\Domain\User\ValueObjects\UserId;

final class OrderPlacedEvent implements DomainEventInterface
{
    private DateTimeImmutable $occurredOn;

    public function __construct(
        private readonly OrderId $orderId,
        private readonly UserId  $userId,
        private readonly Money   $total,
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }

    public function occurredOn(): DateTimeImmutable { return $this->occurredOn; }
    public function eventName(): string             { return 'order.placed'; }
    public function orderId(): OrderId              { return $this->orderId; }
    public function userId(): UserId                { return $this->userId; }
    public function total(): Money                  { return $this->total; }
}

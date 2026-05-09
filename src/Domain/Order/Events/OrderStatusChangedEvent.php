<?php

declare(strict_types=1);

namespace Weale\Domain\Order\Events;

use DateTimeImmutable;
use Weale\Domain\Order\ValueObjects\OrderId;
use Weale\Domain\Order\ValueObjects\OrderStatus;
use Weale\Domain\Shared\Events\DomainEventInterface;

final class OrderStatusChangedEvent implements DomainEventInterface
{
    private DateTimeImmutable $occurredOn;

    public function __construct(
        private readonly OrderId     $orderId,
        private readonly OrderStatus $from,
        private readonly OrderStatus $to,
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }

    public function occurredOn(): DateTimeImmutable { return $this->occurredOn; }
    public function eventName(): string             { return 'order.status_changed'; }
    public function orderId(): OrderId              { return $this->orderId; }
    public function from(): OrderStatus             { return $this->from; }
    public function to(): OrderStatus               { return $this->to; }
}

<?php

declare(strict_types=1);

namespace Weale\Domain\Order;

use DateTimeImmutable;
use DomainException;
use Weale\Domain\Order\Events\OrderPlacedEvent;
use Weale\Domain\Order\Events\OrderStatusChangedEvent;
use Weale\Domain\Order\ValueObjects\OrderId;
use Weale\Domain\Order\ValueObjects\OrderItem;
use Weale\Domain\Order\ValueObjects\OrderStatus;
use Weale\Domain\Product\ValueObjects\Money;
use Weale\Domain\Shared\Events\DomainEventInterface;
use Weale\Domain\User\ValueObjects\UserId;

final class Order
{
    private OrderId $id;
    private UserId $userId;
    /** @var OrderItem[] */
    private array $items;
    private OrderStatus $status;
    private Money $total;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    /** @var DomainEventInterface[] */
    private array $domainEvents = [];

    private function __construct(
        OrderId $id,
        UserId $userId,
        array $items,
        OrderStatus $status,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ) {
        $this->id        = $id;
        $this->userId    = $userId;
        $this->items     = $items;
        $this->status    = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->total     = $this->calculateTotal();
    }

    /**
     * @param OrderItem[] $items
     */
    public static function place(UserId $userId, array $items): self
    {
        if (empty($items)) {
            throw new DomainException('An order must have at least one item.');
        }

        $order = new self(
            id:        OrderId::generate(),
            userId:    $userId,
            items:     $items,
            status:    OrderStatus::PENDING,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $order->recordEvent(new OrderPlacedEvent($order->id, $userId, $order->total));

        return $order;
    }

    public static function reconstitute(
        OrderId $id,
        UserId $userId,
        array $items,
        OrderStatus $status,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        $order              = new self($id, $userId, $items, $status, $createdAt, $updatedAt);
        $order->domainEvents = [];
        return $order;
    }

    public function transitionTo(OrderStatus $newStatus): void
    {
        if (!$this->status->canTransitionTo($newStatus)) {
            throw new DomainException(
                "Cannot transition order from '{$this->status->value}' to '{$newStatus->value}'."
            );
        }

        $old           = $this->status;
        $this->status  = $newStatus;
        $this->updatedAt = new DateTimeImmutable();

        $this->recordEvent(new OrderStatusChangedEvent($this->id, $old, $newStatus));
    }

    public function cancel(): void
    {
        $this->transitionTo(OrderStatus::CANCELLED);
    }

    public function confirm(): void
    {
        $this->transitionTo(OrderStatus::CONFIRMED);
    }

    private function calculateTotal(): Money
    {
        $total = new Money(0, 'BRL');
        foreach ($this->items as $item) {
            $total = $total->add($item->subtotal());
        }
        return $total;
    }

    public function id(): OrderId                  { return $this->id; }
    public function userId(): UserId               { return $this->userId; }
    public function items(): array                 { return $this->items; }
    public function status(): OrderStatus          { return $this->status; }
    public function total(): Money                 { return $this->total; }
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

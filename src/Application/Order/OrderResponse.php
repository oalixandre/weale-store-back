<?php

declare(strict_types=1);

namespace Weale\Application\Order;

use Weale\Domain\Order\Order;
use Weale\Domain\Order\ValueObjects\OrderItem;

final class OrderResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly array  $items,
        public readonly string $status,
        public readonly float  $total,
        public readonly string $currency,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {}

    public static function fromOrder(Order $order): self
    {
        return new self(
            id:        $order->id()->value(),
            userId:    $order->userId()->value(),
            items:     array_map(fn (OrderItem $i) => [
                'product_id'   => $i->productId()->value(),
                'product_name' => $i->productName(),
                'quantity'     => $i->quantity(),
                'unit_price'   => $i->unitPrice()->amount(),
                'subtotal'     => $i->subtotal()->amount(),
            ], $order->items()),
            status:    $order->status()->value,
            total:     $order->total()->amount(),
            currency:  $order->total()->currency(),
            createdAt: $order->createdAt()->format(DATE_ATOM),
            updatedAt: $order->updatedAt()->format(DATE_ATOM),
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'user_id'    => $this->userId,
            'items'      => $this->items,
            'status'     => $this->status,
            'total'      => $this->total,
            'currency'   => $this->currency,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}

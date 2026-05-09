<?php

declare(strict_types=1);

namespace Weale\Application\Order;

final class PlaceOrderCommand
{
    /**
     * @param array<array{product_id: string, quantity: int}> $items
     */
    public function __construct(
        public readonly string $userId,
        public readonly array  $items,
    ) {}
}

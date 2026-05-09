<?php

declare(strict_types=1);

namespace Weale\Domain\Order\ValueObjects;

use InvalidArgumentException;
use Weale\Domain\Product\ValueObjects\Money;
use Weale\Domain\Product\ValueObjects\ProductId;

final class OrderItem
{
    private function __construct(
        private readonly ProductId $productId,
        private readonly string    $productName,
        private readonly int       $quantity,
        private readonly Money     $unitPrice,
    ) {}

    public static function create(
        ProductId $productId,
        string $productName,
        int $quantity,
        Money $unitPrice,
    ): self {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Order item quantity must be positive.');
        }
        return new self($productId, $productName, $quantity, $unitPrice);
    }

    public function subtotal(): Money
    {
        return new Money($this->unitPrice->amountInCents() * $this->quantity, $this->unitPrice->currency());
    }

    public function productId(): ProductId  { return $this->productId; }
    public function productName(): string   { return $this->productName; }
    public function quantity(): int         { return $this->quantity; }
    public function unitPrice(): Money      { return $this->unitPrice; }
}

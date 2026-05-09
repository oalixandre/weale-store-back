<?php

declare(strict_types=1);

namespace Weale\Domain\Product\Events;

use DateTimeImmutable;
use Weale\Domain\Product\ValueObjects\ProductId;
use Weale\Domain\Shared\Events\DomainEventInterface;

final class ProductCreatedEvent implements DomainEventInterface
{
    private DateTimeImmutable $occurredOn;

    public function __construct(
        private readonly ProductId $productId,
        private readonly string $productName,
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function eventName(): string
    {
        return 'product.created';
    }

    public function productId(): ProductId
    {
        return $this->productId;
    }

    public function productName(): string
    {
        return $this->productName;
    }
}

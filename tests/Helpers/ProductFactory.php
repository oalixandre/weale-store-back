<?php

declare(strict_types=1);

namespace Weale\Tests\Helpers;

use Weale\Domain\Product\Product;
use Weale\Domain\Product\ValueObjects\Money;

final class ProductFactory
{
    public static function make(array $overrides = []): Product
    {
        return Product::create(
            name:        $overrides['name']        ?? 'Test Product ' . uniqid(),
            description: $overrides['description'] ?? 'A test product description',
            price:       $overrides['price']       ?? Money::fromFloat(99.99),
            stock:       $overrides['stock']       ?? 10,
            category:    $overrides['category']    ?? 'electronics',
        );
    }

    public static function makeMany(int $count, array $overrides = []): array
    {
        return array_map(fn () => self::make($overrides), range(1, $count));
    }
}

<?php

declare(strict_types=1);

namespace Weale\Application\Product;

final class UpdateProductCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly float  $price,
        public readonly string $category,
    ) {}
}

<?php

declare(strict_types=1);

namespace Weale\Application\Product;

use Weale\Domain\Product\Product;

final class ProductResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $description,
        public readonly float  $price,
        public readonly string $currency,
        public readonly int    $stock,
        public readonly string $category,
        public readonly bool   $active,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {}

    public static function fromProduct(Product $product): self
    {
        return new self(
            id:          $product->id()->value(),
            name:        $product->name(),
            description: $product->description(),
            price:       $product->price()->amount(),
            currency:    $product->price()->currency(),
            stock:       $product->stock(),
            category:    $product->category(),
            active:      $product->isActive(),
            createdAt:   $product->createdAt()->format(DATE_ATOM),
            updatedAt:   $product->updatedAt()->format(DATE_ATOM),
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'price'       => $this->price,
            'currency'    => $this->currency,
            'stock'       => $this->stock,
            'category'    => $this->category,
            'active'      => $this->active,
            'created_at'  => $this->createdAt,
            'updated_at'  => $this->updatedAt,
        ];
    }
}

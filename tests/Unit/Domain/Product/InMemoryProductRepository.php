<?php

declare(strict_types=1);

namespace Weale\Tests\Unit\Domain\Product;

use Weale\Domain\Product\Product;
use Weale\Domain\Product\ProductRepositoryInterface;
use Weale\Domain\Product\ValueObjects\ProductId;

final class InMemoryProductRepository implements ProductRepositoryInterface
{
    /** @var Product[] */
    private array $products = [];

    public function findById(ProductId $id): ?Product
    {
        return $this->products[$id->value()] ?? null;
    }

    public function findAll(int $page = 1, int $perPage = 20): array
    {
        $all = array_values($this->products);
        return array_slice($all, ($page - 1) * $perPage, $perPage);
    }

    public function findByCategory(string $category): array
    {
        return array_values(
            array_filter($this->products, fn ($p) => $p->category() === $category)
        );
    }

    public function save(Product $product): void
    {
        $this->products[$product->id()->value()] = $product;
    }

    public function delete(ProductId $id): void
    {
        unset($this->products[$id->value()]);
    }

    public function count(): int
    {
        return count($this->products);
    }
}

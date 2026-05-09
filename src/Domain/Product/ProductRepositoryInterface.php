<?php

declare(strict_types=1);

namespace Weale\Domain\Product;

use Weale\Domain\Product\ValueObjects\ProductId;

interface ProductRepositoryInterface
{
    public function findById(ProductId $id): ?Product;

    /** @return Product[] */
    public function findAll(int $page = 1, int $perPage = 20): array;

    public function findByCategory(string $category): array;

    public function save(Product $product): void;

    public function delete(ProductId $id): void;

    public function count(): int;
}

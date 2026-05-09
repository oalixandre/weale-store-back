<?php

declare(strict_types=1);

namespace Weale\Application\Product;

use Weale\Domain\Product\ProductRepositoryInterface;
use Weale\Domain\Product\ValueObjects\ProductId;
use Weale\Domain\Shared\Exceptions\NotFoundException;

final class DeleteProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    public function execute(string $id): void
    {
        $productId = ProductId::fromString($id);
        $product   = $this->repository->findById($productId);

        if ($product === null) {
            throw new NotFoundException("Product not found with id: {$id}");
        }

        $this->repository->delete($productId);
    }
}

<?php

declare(strict_types=1);

namespace Weale\Application\Product;

use Weale\Domain\Product\ProductRepositoryInterface;
use Weale\Domain\Product\ValueObjects\Money;
use Weale\Domain\Product\ValueObjects\ProductId;
use Weale\Domain\Shared\Exceptions\NotFoundException;

final class UpdateProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    public function execute(string $id, UpdateProductCommand $command): ProductResponse
    {
        $product = $this->repository->findById(ProductId::fromString($id));

        if ($product === null) {
            throw new NotFoundException("Product not found: {$id}");
        }

        $product->update(
            name:        $command->name,
            description: $command->description,
            price:       Money::fromFloat($command->price),
            category:    $command->category,
        );

        $this->repository->save($product);

        return ProductResponse::fromProduct($product);
    }
}

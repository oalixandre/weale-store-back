<?php

declare(strict_types=1);

namespace Weale\Application\Product;

use Weale\Domain\Product\Product;
use Weale\Domain\Product\ProductRepositoryInterface;
use Weale\Domain\Product\ValueObjects\Money;

final class CreateProductUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    public function execute(CreateProductCommand $command): ProductResponse
    {
        $product = Product::create(
            name:        $command->name,
            description: $command->description,
            price:       Money::fromFloat($command->price),
            stock:       $command->stock,
            category:    $command->category,
        );

        $this->repository->save($product);

        return ProductResponse::fromProduct($product);
    }
}

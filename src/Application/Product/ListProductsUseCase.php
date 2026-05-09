<?php

declare(strict_types=1);

namespace Weale\Application\Product;

use Weale\Domain\Product\ProductRepositoryInterface;

final class ListProductsUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository,
    ) {}

    /**
     * @return array{data: ProductResponse[], total: int, page: int, per_page: int}
     */
    public function execute(int $page = 1, int $perPage = 20): array
    {
        $products = $this->repository->findAll($page, $perPage);
        $total    = $this->repository->count();

        return [
            'data'     => array_map(
                fn ($product) => ProductResponse::fromProduct($product),
                $products
            ),
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ];
    }
}

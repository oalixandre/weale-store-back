<?php

declare(strict_types=1);

namespace Weale\Tests\Unit\Application\Product;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Weale\Application\Product\GetProductUseCase;
use Weale\Domain\Product\Product;
use Weale\Domain\Product\ProductRepositoryInterface;
use Weale\Domain\Product\ValueObjects\Money;
use Weale\Domain\Product\ValueObjects\ProductId;
use Weale\Domain\Shared\Exceptions\NotFoundException;

final class GetProductUseCaseTest extends TestCase
{
    private MockInterface&ProductRepositoryInterface $repository;
    private GetProductUseCase $useCase;

    protected function setUp(): void
    {
        $this->repository = Mockery::mock(ProductRepositoryInterface::class);
        $this->useCase    = new GetProductUseCase($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_it_returns_product_response(): void
    {
        $product = Product::create(
            name:        'Notebook',
            description: 'A laptop',
            price:       Money::fromFloat(3500.00),
            stock:       5,
            category:    'computers',
        );

        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->andReturn($product);

        $response = $this->useCase->execute($product->id()->value());

        $this->assertEquals('Notebook', $response->name);
        $this->assertEquals('computers', $response->category);
    }

    public function test_it_throws_not_found_exception(): void
    {
        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->andReturn(null);

        $this->expectException(NotFoundException::class);

        $id = ProductId::generate()->value();
        $this->useCase->execute($id);
    }
}

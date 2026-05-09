<?php

declare(strict_types=1);

namespace Weale\Tests\Unit\Application\Product;

use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Weale\Application\Product\CreateProductCommand;
use Weale\Application\Product\CreateProductUseCase;
use Weale\Application\Product\ProductResponse;
use Weale\Domain\Product\ProductRepositoryInterface;

final class CreateProductUseCaseTest extends TestCase
{
    private MockInterface&ProductRepositoryInterface $repository;
    private CreateProductUseCase $useCase;

    protected function setUp(): void
    {
        $this->repository = Mockery::mock(ProductRepositoryInterface::class);
        $this->useCase    = new CreateProductUseCase($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_it_creates_product_and_returns_response(): void
    {
        $this->repository
            ->shouldReceive('save')
            ->once();

        $command = new CreateProductCommand(
            name:        'iPhone 15',
            description: 'Apple smartphone',
            price:       5999.99,
            stock:       50,
            category:    'smartphones',
        );

        $response = $this->useCase->execute($command);

        $this->assertInstanceOf(ProductResponse::class, $response);
        $this->assertEquals('iPhone 15', $response->name);
        $this->assertEquals(599999, (int) round($response->price * 100));
        $this->assertEquals(50, $response->stock);
        $this->assertTrue($response->active);
        $this->assertNotEmpty($response->id);
    }

    public function test_it_throws_when_name_is_empty(): void
    {
        $this->repository->shouldNotReceive('save');

        $this->expectException(InvalidArgumentException::class);

        $command = new CreateProductCommand(
            name:        '',
            description: 'desc',
            price:       10.0,
            stock:       1,
            category:    'cat',
        );

        $this->useCase->execute($command);
    }
}

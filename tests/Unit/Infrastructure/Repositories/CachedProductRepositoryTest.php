<?php

declare(strict_types=1);

namespace Weale\Tests\Unit\Infrastructure\Repositories;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Weale\Domain\Product\ProductRepositoryInterface;
use Weale\Domain\Product\ValueObjects\ProductId;
use Weale\Infrastructure\Cache\NullCache;
use Weale\Infrastructure\Persistence\Repositories\CachedProductRepository;
use Weale\Tests\Helpers\ProductFactory;

final class CachedProductRepositoryTest extends TestCase
{
    private MockInterface&ProductRepositoryInterface $inner;
    private CachedProductRepository $repo;

    protected function setUp(): void
    {
        $this->inner = Mockery::mock(ProductRepositoryInterface::class);
        $this->repo  = new CachedProductRepository($this->inner, new NullCache());
    }

    protected function tearDown(): void { Mockery::close(); }

    public function test_find_by_id_delegates_to_inner(): void
    {
        $product = ProductFactory::make();
        $this->inner->shouldReceive('findById')->once()->andReturn($product);

        $result = $this->repo->findById($product->id());

        $this->assertSame($product, $result);
    }

    public function test_find_all_delegates_to_inner(): void
    {
        $products = ProductFactory::makeMany(3);
        $this->inner->shouldReceive('findAll')->once()->andReturn($products);

        $result = $this->repo->findAll(1, 20);

        $this->assertSame($products, $result);
    }

    public function test_save_invalidates_and_delegates(): void
    {
        $product = ProductFactory::make();
        $this->inner->shouldReceive('save')->once()->with($product);

        $this->repo->save($product);
    }

    public function test_delete_delegates_to_inner(): void
    {
        $id = ProductId::generate();
        $this->inner->shouldReceive('delete')->once()->with(
            Mockery::on(fn ($arg) => $arg->equals($id))
        );

        $this->repo->delete($id);
    }

    public function test_count_delegates_to_inner(): void
    {
        $this->inner->shouldReceive('count')->once()->andReturn(42);
        $this->assertEquals(42, $this->repo->count());
    }
}

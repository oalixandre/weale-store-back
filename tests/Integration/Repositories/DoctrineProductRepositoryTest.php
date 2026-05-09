<?php

declare(strict_types=1);

namespace Weale\Tests\Integration\Repositories;

use Weale\Domain\Product\ValueObjects\ProductId;
use Weale\Infrastructure\Persistence\Repositories\DoctrineProductRepository;
use Weale\Tests\Helpers\DatabaseTestCase;
use Weale\Tests\Helpers\ProductFactory;

/**
 * @group integration
 */
final class DoctrineProductRepositoryTest extends DatabaseTestCase
{
    private DoctrineProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DoctrineProductRepository($this->em);
    }

    public function test_it_saves_and_finds_product_by_id(): void
    {
        $product = ProductFactory::make(['name' => 'Notebook Pro', 'stock' => 5]);
        $this->repository->save($product);
        $this->em->clear();

        $found = $this->repository->findById($product->id());

        $this->assertNotNull($found);
        $this->assertEquals('Notebook Pro', $found->name());
        $this->assertEquals(5, $found->stock());
    }

    public function test_it_returns_null_for_unknown_id(): void
    {
        $result = $this->repository->findById(ProductId::generate());
        $this->assertNull($result);
    }

    public function test_it_lists_products_paginated(): void
    {
        foreach (ProductFactory::makeMany(5) as $p) {
            $this->repository->save($p);
        }
        $this->em->clear();

        $page1 = $this->repository->findAll(1, 3);
        $page2 = $this->repository->findAll(2, 3);

        $this->assertCount(3, $page1);
        $this->assertCount(2, $page2);
    }

    public function test_it_counts_products(): void
    {
        foreach (ProductFactory::makeMany(4) as $p) {
            $this->repository->save($p);
        }

        $this->assertEquals(4, $this->repository->count());
    }

    public function test_it_deletes_a_product(): void
    {
        $product = ProductFactory::make();
        $this->repository->save($product);
        $this->em->clear();

        $this->repository->delete($product->id());

        $found = $this->repository->findById($product->id());
        $this->assertNull($found);
    }

    public function test_it_updates_a_product(): void
    {
        $product = ProductFactory::make(['name' => 'Old Name']);
        $this->repository->save($product);
        $this->em->clear();

        $found = $this->repository->findById($product->id());
        $found->update('New Name', 'New desc', $found->price(), $found->category());
        $this->repository->save($found);
        $this->em->clear();

        $updated = $this->repository->findById($product->id());
        $this->assertEquals('New Name', $updated->name());
    }

    public function test_it_finds_products_by_category(): void
    {
        $this->repository->save(ProductFactory::make(['category' => 'books']));
        $this->repository->save(ProductFactory::make(['category' => 'books']));
        $this->repository->save(ProductFactory::make(['category' => 'electronics']));

        $books = $this->repository->findByCategory('books');

        $this->assertCount(2, $books);
    }
}

<?php

declare(strict_types=1);

namespace Weale\Tests\Integration\UseCases;

use DomainException;
use Weale\Application\Order\PlaceOrderCommand;
use Weale\Application\Order\PlaceOrderUseCase;
use Weale\Infrastructure\Persistence\Repositories\DoctrineOrderRepository;
use Weale\Infrastructure\Persistence\Repositories\DoctrineProductRepository;
use Weale\Tests\Helpers\DatabaseTestCase;
use Weale\Tests\Helpers\ProductFactory;
use Weale\Tests\Helpers\UserFactory;

/**
 * @group integration
 */
final class PlaceOrderUseCaseTest extends DatabaseTestCase
{
    private PlaceOrderUseCase $useCase;
    private DoctrineProductRepository $productRepo;
    private DoctrineOrderRepository $orderRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productRepo = new DoctrineProductRepository($this->em);
        $this->orderRepo   = new DoctrineOrderRepository($this->em);
        $this->useCase     = new PlaceOrderUseCase($this->orderRepo, $this->productRepo);
    }

    public function test_it_places_order_and_decreases_stock(): void
    {
        $user    = UserFactory::make();
        $product = ProductFactory::make(['stock' => 10, 'price' => \Weale\Domain\Product\ValueObjects\Money::fromFloat(50.0)]);
        $this->productRepo->save($product);
        $this->em->clear();

        $command = new PlaceOrderCommand(
            userId: $user->id()->value(),
            items:  [['product_id' => $product->id()->value(), 'quantity' => 3]],
        );

        $response = $this->useCase->execute($command);

        $this->assertEquals('pending', $response->status);
        $this->assertEquals(150.0, $response->total);
        $this->assertCount(1, $response->items);

        // Stock must have decreased
        $this->em->clear();
        $updated = $this->productRepo->findById($product->id());
        $this->assertEquals(7, $updated->stock());
    }

    public function test_it_throws_on_insufficient_stock(): void
    {
        $product = ProductFactory::make(['stock' => 2]);
        $this->productRepo->save($product);
        $this->em->clear();

        $this->expectException(DomainException::class);

        $user = UserFactory::make();
        $this->useCase->execute(new PlaceOrderCommand(
            userId: $user->id()->value(),
            items:  [['product_id' => $product->id()->value(), 'quantity' => 99]],
        ));
    }
}

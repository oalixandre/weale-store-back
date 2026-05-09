<?php

declare(strict_types=1);

namespace Weale\Tests\Unit\Domain\Product;

use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Weale\Domain\Product\Events\ProductCreatedEvent;
use Weale\Domain\Product\Product;
use Weale\Domain\Product\ValueObjects\Money;

final class ProductTest extends TestCase
{
    private function makeProduct(
        string $name        = 'Test Product',
        string $description = 'A test product',
        float  $price       = 99.99,
        int    $stock       = 10,
        string $category    = 'electronics',
    ): Product {
        return Product::create(
            name:        $name,
            description: $description,
            price:       Money::fromFloat($price),
            stock:       $stock,
            category:    $category,
        );
    }

    public function test_it_can_be_created(): void
    {
        $product = $this->makeProduct();

        $this->assertEquals('Test Product', $product->name());
        $this->assertEquals('A test product', $product->description());
        $this->assertEquals(9999, $product->price()->amountInCents());
        $this->assertEquals(10, $product->stock());
        $this->assertEquals('electronics', $product->category());
        $this->assertTrue($product->isActive());
    }

    public function test_it_records_created_event(): void
    {
        $product = $this->makeProduct();
        $events  = $product->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProductCreatedEvent::class, $events[0]);
    }

    public function test_it_clears_events_after_pull(): void
    {
        $product = $this->makeProduct();
        $product->pullDomainEvents();

        $this->assertEmpty($product->pullDomainEvents());
    }

    public function test_it_throws_on_empty_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->makeProduct(name: '');
    }

    public function test_it_throws_on_negative_stock(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->makeProduct(stock: -1);
    }

    public function test_it_can_add_stock(): void
    {
        $product = $this->makeProduct(stock: 5);
        $product->addStock(10);

        $this->assertEquals(15, $product->stock());
    }

    public function test_it_throws_when_adding_non_positive_stock(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->makeProduct()->addStock(0);
    }

    public function test_it_can_decrease_stock(): void
    {
        $product = $this->makeProduct(stock: 10);
        $product->decreaseStock(3);

        $this->assertEquals(7, $product->stock());
    }

    public function test_it_throws_on_insufficient_stock(): void
    {
        $this->expectException(DomainException::class);
        $this->makeProduct(stock: 5)->decreaseStock(10);
    }

    public function test_it_can_be_deactivated(): void
    {
        $product = $this->makeProduct();
        $product->deactivate();

        $this->assertFalse($product->isActive());
    }

    public function test_it_can_be_reactivated(): void
    {
        $product = $this->makeProduct();
        $product->deactivate();
        $product->activate();

        $this->assertTrue($product->isActive());
    }

    public function test_it_can_be_updated(): void
    {
        $product = $this->makeProduct();
        $product->update('New Name', 'New Desc', Money::fromFloat(199.99), 'clothing');

        $this->assertEquals('New Name', $product->name());
        $this->assertEquals('New Desc', $product->description());
        $this->assertEquals(19999, $product->price()->amountInCents());
        $this->assertEquals('clothing', $product->category());
    }
}

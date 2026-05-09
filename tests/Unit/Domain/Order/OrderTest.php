<?php

declare(strict_types=1);

namespace Weale\Tests\Unit\Domain\Order;

use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Weale\Domain\Order\Events\OrderPlacedEvent;
use Weale\Domain\Order\Order;
use Weale\Domain\Order\ValueObjects\OrderItem;
use Weale\Domain\Order\ValueObjects\OrderStatus;
use Weale\Domain\Product\ValueObjects\Money;
use Weale\Domain\Product\ValueObjects\ProductId;
use Weale\Domain\User\ValueObjects\UserId;

final class OrderTest extends TestCase
{
    private function makeItem(float $price = 100.0, int $qty = 1): OrderItem
    {
        return OrderItem::create(
            productId:   ProductId::generate(),
            productName: 'Test Product',
            quantity:    $qty,
            unitPrice:   Money::fromFloat($price),
        );
    }

    private function makeOrder(array $items = []): Order
    {
        return Order::place(
            userId: UserId::generate(),
            items:  $items ?: [$this->makeItem()],
        );
    }

    public function test_it_can_be_placed(): void
    {
        $order = $this->makeOrder();

        $this->assertEquals(OrderStatus::PENDING, $order->status());
        $this->assertCount(1, $order->items());
    }

    public function test_it_records_placed_event(): void
    {
        $order  = $this->makeOrder();
        $events = $order->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(OrderPlacedEvent::class, $events[0]);
    }

    public function test_it_throws_on_empty_items(): void
    {
        $this->expectException(DomainException::class);
        Order::place(UserId::generate(), []);
    }

    public function test_it_calculates_total_correctly(): void
    {
        $items = [
            $this->makeItem(price: 50.0, qty: 2),
            $this->makeItem(price: 30.0, qty: 1),
        ];
        $order = $this->makeOrder($items);

        $this->assertEquals(13000, $order->total()->amountInCents()); // 100 + 30 = 130.00
    }

    public function test_it_can_be_confirmed(): void
    {
        $order = $this->makeOrder();
        $order->confirm();
        $this->assertEquals(OrderStatus::CONFIRMED, $order->status());
    }

    public function test_it_can_be_cancelled(): void
    {
        $order = $this->makeOrder();
        $order->cancel();
        $this->assertEquals(OrderStatus::CANCELLED, $order->status());
    }

    public function test_it_throws_on_invalid_transition(): void
    {
        $this->expectException(DomainException::class);
        $order = $this->makeOrder();
        $order->transitionTo(OrderStatus::DELIVERED); // pending -> delivered is invalid
    }

    public function test_it_cannot_transition_from_final_status(): void
    {
        $this->expectException(DomainException::class);
        $order = $this->makeOrder();
        $order->cancel();
        $order->confirm(); // cannot leave cancelled
    }
}

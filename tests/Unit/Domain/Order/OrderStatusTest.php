<?php

declare(strict_types=1);

namespace Weale\Tests\Unit\Domain\Order;

use PHPUnit\Framework\TestCase;
use Weale\Domain\Order\ValueObjects\OrderStatus;

final class OrderStatusTest extends TestCase
{
    public function test_pending_can_go_to_confirmed(): void
    {
        $this->assertTrue(OrderStatus::PENDING->canTransitionTo(OrderStatus::CONFIRMED));
    }

    public function test_pending_can_go_to_cancelled(): void
    {
        $this->assertTrue(OrderStatus::PENDING->canTransitionTo(OrderStatus::CANCELLED));
    }

    public function test_pending_cannot_go_to_delivered(): void
    {
        $this->assertFalse(OrderStatus::PENDING->canTransitionTo(OrderStatus::DELIVERED));
    }

    public function test_confirmed_can_go_to_shipped(): void
    {
        $this->assertTrue(OrderStatus::CONFIRMED->canTransitionTo(OrderStatus::SHIPPED));
    }

    public function test_shipped_can_go_to_delivered(): void
    {
        $this->assertTrue(OrderStatus::SHIPPED->canTransitionTo(OrderStatus::DELIVERED));
    }

    public function test_delivered_is_final(): void
    {
        $this->assertTrue(OrderStatus::DELIVERED->isFinal());
        $this->assertFalse(OrderStatus::DELIVERED->canTransitionTo(OrderStatus::CANCELLED));
    }

    public function test_cancelled_is_final(): void
    {
        $this->assertTrue(OrderStatus::CANCELLED->isFinal());
    }
}

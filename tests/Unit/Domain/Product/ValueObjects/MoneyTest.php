<?php

declare(strict_types=1);

namespace Weale\Tests\Unit\Domain\Product\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Weale\Domain\Product\ValueObjects\Money;

final class MoneyTest extends TestCase
{
    public function test_it_can_be_created_from_cents(): void
    {
        $money = new Money(1999, 'BRL');

        $this->assertEquals(1999, $money->amountInCents());
        $this->assertEquals(19.99, $money->amount());
        $this->assertEquals('BRL', $money->currency());
    }

    public function test_it_can_be_created_from_float(): void
    {
        $money = Money::fromFloat(19.99, 'BRL');

        $this->assertEquals(1999, $money->amountInCents());
    }

    public function test_it_throws_on_negative_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Money(-1);
    }

    public function test_it_can_add_money(): void
    {
        $a      = new Money(1000, 'BRL');
        $b      = new Money(500, 'BRL');
        $result = $a->add($b);

        $this->assertEquals(1500, $result->amountInCents());
    }

    public function test_it_can_subtract_money(): void
    {
        $a      = new Money(1000, 'BRL');
        $b      = new Money(300, 'BRL');
        $result = $a->subtract($b);

        $this->assertEquals(700, $result->amountInCents());
    }

    public function test_it_throws_when_adding_different_currencies(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new Money(100, 'BRL'))->add(new Money(100, 'USD'));
    }

    public function test_it_equals_another_money_with_same_value(): void
    {
        $a = new Money(1000, 'BRL');
        $b = new Money(1000, 'BRL');

        $this->assertTrue($a->equals($b));
    }

    public function test_zero_is_valid(): void
    {
        $money = new Money(0);
        $this->assertEquals(0, $money->amountInCents());
    }
}

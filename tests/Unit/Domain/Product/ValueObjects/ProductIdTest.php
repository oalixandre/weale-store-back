<?php

declare(strict_types=1);

namespace Weale\Tests\Unit\Domain\Product\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Weale\Domain\Product\ValueObjects\ProductId;

final class ProductIdTest extends TestCase
{
    public function test_it_can_be_generated(): void
    {
        $id = ProductId::generate();

        $this->assertNotEmpty($id->value());
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $id->value()
        );
    }

    public function test_it_can_be_created_from_string(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id   = ProductId::fromString($uuid);

        $this->assertEquals($uuid, $id->value());
    }

    public function test_it_throws_on_invalid_uuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ProductId::fromString('not-a-valid-uuid');
    }

    public function test_two_ids_with_same_value_are_equal(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id1  = ProductId::fromString($uuid);
        $id2  = ProductId::fromString($uuid);

        $this->assertTrue($id1->equals($id2));
    }

    public function test_two_different_ids_are_not_equal(): void
    {
        $id1 = ProductId::generate();
        $id2 = ProductId::generate();

        $this->assertFalse($id1->equals($id2));
    }

    public function test_it_casts_to_string(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $id   = ProductId::fromString($uuid);

        $this->assertEquals($uuid, (string) $id);
    }
}

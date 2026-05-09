<?php

declare(strict_types=1);

namespace Weale\Tests\Unit\Domain\User;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Weale\Domain\User\ValueObjects\Email;

final class EmailTest extends TestCase
{
    public function test_it_normalizes_to_lowercase(): void
    {
        $email = new Email('JOHN@EXAMPLE.COM');
        $this->assertEquals('john@example.com', $email->value());
    }

    public function test_it_throws_on_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Email('not-an-email');
    }

    public function test_two_equal_emails_are_equal(): void
    {
        $a = new Email('john@example.com');
        $b = new Email('john@example.com');
        $this->assertTrue($a->equals($b));
    }

    public function test_it_casts_to_string(): void
    {
        $email = new Email('test@example.com');
        $this->assertEquals('test@example.com', (string) $email);
    }
}

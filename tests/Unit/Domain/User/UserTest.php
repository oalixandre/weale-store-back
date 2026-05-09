<?php

declare(strict_types=1);

namespace Weale\Tests\Unit\Domain\User;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Weale\Domain\User\Events\UserRegisteredEvent;
use Weale\Domain\User\User;
use Weale\Domain\User\ValueObjects\Email;
use Weale\Domain\User\ValueObjects\HashedPassword;
use Weale\Domain\User\ValueObjects\UserRole;

final class UserTest extends TestCase
{
    private function makeUser(
        string $name     = 'John Doe',
        string $email    = 'john@example.com',
        string $password = 'secret123',
    ): User {
        return User::register(
            name:     $name,
            email:    new Email($email),
            password: HashedPassword::fromPlainText($password),
        );
    }

    public function test_it_can_be_registered(): void
    {
        $user = $this->makeUser();

        $this->assertEquals('John Doe', $user->name());
        $this->assertEquals('john@example.com', $user->email()->value());
        $this->assertEquals(UserRole::CUSTOMER, $user->role());
        $this->assertTrue($user->isActive());
    }

    public function test_it_records_registered_event(): void
    {
        $user   = $this->makeUser();
        $events = $user->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(UserRegisteredEvent::class, $events[0]);
    }

    public function test_it_verifies_password(): void
    {
        $user = $this->makeUser(password: 'mypassword');
        $this->assertTrue($user->verifyPassword('mypassword'));
        $this->assertFalse($user->verifyPassword('wrongpassword'));
    }

    public function test_it_throws_on_empty_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->makeUser(name: '');
    }

    public function test_it_can_be_deactivated_and_reactivated(): void
    {
        $user = $this->makeUser();
        $user->deactivate();
        $this->assertFalse($user->isActive());

        $user->activate();
        $this->assertTrue($user->isActive());
    }

    public function test_it_can_change_name(): void
    {
        $user = $this->makeUser();
        $user->changeName('Jane Doe');
        $this->assertEquals('Jane Doe', $user->name());
    }
}

<?php

declare(strict_types=1);

namespace Weale\Tests\Unit\Application\User;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Weale\Application\User\RegisterUserCommand;
use Weale\Application\User\RegisterUserUseCase;
use Weale\Domain\Shared\Exceptions\DomainException;
use Weale\Domain\User\UserRepositoryInterface;
use Weale\Domain\User\ValueObjects\Email;

final class RegisterUserUseCaseTest extends TestCase
{
    private MockInterface&UserRepositoryInterface $repository;
    private RegisterUserUseCase $useCase;

    protected function setUp(): void
    {
        $this->repository = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase    = new RegisterUserUseCase($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_it_registers_user(): void
    {
        $this->repository->shouldReceive('existsByEmail')->once()->andReturn(false);
        $this->repository->shouldReceive('save')->once();

        $command  = new RegisterUserCommand('John', 'john@example.com', 'password123');
        $response = $this->useCase->execute($command);

        $this->assertEquals('John', $response->name);
        $this->assertEquals('john@example.com', $response->email);
        $this->assertEquals('customer', $response->role);
        $this->assertTrue($response->active);
    }

    public function test_it_throws_when_email_already_taken(): void
    {
        $this->repository->shouldReceive('existsByEmail')->once()->andReturn(true);
        $this->repository->shouldNotReceive('save');

        $this->expectException(DomainException::class);

        $this->useCase->execute(new RegisterUserCommand('Jane', 'taken@example.com', 'password123'));
    }

    public function test_it_throws_on_invalid_email(): void
    {
        $this->repository->shouldNotReceive('existsByEmail');
        $this->expectException(\InvalidArgumentException::class);
        $this->useCase->execute(new RegisterUserCommand('Bob', 'not-email', 'password123'));
    }
}

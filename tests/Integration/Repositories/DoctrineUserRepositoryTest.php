<?php

declare(strict_types=1);

namespace Weale\Tests\Integration\Repositories;

use Weale\Domain\User\ValueObjects\Email;
use Weale\Infrastructure\Persistence\Repositories\DoctrineUserRepository;
use Weale\Tests\Helpers\DatabaseTestCase;
use Weale\Tests\Helpers\UserFactory;

/**
 * @group integration
 */
final class DoctrineUserRepositoryTest extends DatabaseTestCase
{
    private DoctrineUserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new DoctrineUserRepository($this->em);
    }

    public function test_it_saves_and_finds_user_by_id(): void
    {
        $user = UserFactory::make(['name' => 'Alice', 'email' => 'alice@example.com']);
        $this->repository->save($user);
        $this->em->clear();

        $found = $this->repository->findById($user->id());

        $this->assertNotNull($found);
        $this->assertEquals('Alice', $found->name());
        $this->assertEquals('alice@example.com', $found->email()->value());
    }

    public function test_it_finds_user_by_email(): void
    {
        $user = UserFactory::make(['email' => 'bob@example.com']);
        $this->repository->save($user);
        $this->em->clear();

        $found = $this->repository->findByEmail(new Email('bob@example.com'));

        $this->assertNotNull($found);
        $this->assertEquals('bob@example.com', $found->email()->value());
    }

    public function test_it_returns_null_for_unknown_email(): void
    {
        $result = $this->repository->findByEmail(new Email('nobody@example.com'));
        $this->assertNull($result);
    }

    public function test_it_detects_existing_email(): void
    {
        $user = UserFactory::make(['email' => 'exists@example.com']);
        $this->repository->save($user);

        $this->assertTrue($this->repository->existsByEmail(new Email('exists@example.com')));
        $this->assertFalse($this->repository->existsByEmail(new Email('nope@example.com')));
    }

    public function test_it_verifies_password_after_reconstitution(): void
    {
        $user = UserFactory::make(['password' => 'supersecret']);
        $this->repository->save($user);
        $this->em->clear();

        $found = $this->repository->findById($user->id());

        $this->assertTrue($found->verifyPassword('supersecret'));
        $this->assertFalse($found->verifyPassword('wrongpassword'));
    }
}

<?php

declare(strict_types=1);

namespace Weale\Infrastructure\Persistence\Repositories;

use Doctrine\ORM\EntityManagerInterface;
use Weale\Domain\User\User;
use Weale\Domain\User\UserRepositoryInterface;
use Weale\Domain\User\ValueObjects\Email;
use Weale\Domain\User\ValueObjects\HashedPassword;
use Weale\Domain\User\ValueObjects\UserId;
use Weale\Domain\User\ValueObjects\UserRole;
use Weale\Infrastructure\Persistence\Entities\UserEntity;

final class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public function findById(UserId $id): ?User
    {
        $entity = $this->em->find(UserEntity::class, $id->value());
        return $entity ? $this->toDomain($entity) : null;
    }

    public function findByEmail(Email $email): ?User
    {
        $entity = $this->em->getRepository(UserEntity::class)
            ->findOneBy(['email' => $email->value()]);
        return $entity ? $this->toDomain($entity) : null;
    }

    public function save(User $user): void
    {
        $entity = $this->em->find(UserEntity::class, $user->id()->value()) ?? new UserEntity();
        $this->hydrateEntity($entity, $user);
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function delete(UserId $id): void
    {
        $entity = $this->em->find(UserEntity::class, $id->value());
        if ($entity) {
            $this->em->remove($entity);
            $this->em->flush();
        }
    }

    public function existsByEmail(Email $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    private function toDomain(UserEntity $e): User
    {
        return User::reconstitute(
            id:        UserId::fromString($e->id),
            name:      $e->name,
            email:     new Email($e->email),
            password:  HashedPassword::fromHash($e->passwordHash),
            role:      UserRole::from($e->role),
            active:    $e->active,
            createdAt: $e->createdAt,
            updatedAt: $e->updatedAt,
        );
    }

    private function hydrateEntity(UserEntity $e, User $user): void
    {
        $e->id           = $user->id()->value();
        $e->name         = $user->name();
        $e->email        = $user->email()->value();
        $e->passwordHash = $user->password()->hash();
        $e->role         = $user->role()->value;
        $e->active       = $user->isActive();
        $e->createdAt    = $user->createdAt();
        $e->updatedAt    = $user->updatedAt();
    }
}

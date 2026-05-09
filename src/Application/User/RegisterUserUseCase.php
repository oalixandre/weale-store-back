<?php

declare(strict_types=1);

namespace Weale\Application\User;

use Weale\Domain\Shared\EventDispatcher\EventDispatcherInterface;
use Weale\Domain\Shared\Exceptions\DomainException;
use Weale\Domain\User\User;
use Weale\Domain\User\UserRepositoryInterface;
use Weale\Domain\User\ValueObjects\Email;
use Weale\Domain\User\ValueObjects\HashedPassword;
use Weale\Domain\User\ValueObjects\UserRole;

final class RegisterUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface  $repository,
        private readonly EventDispatcherInterface $dispatcher,
    ) {}

    public function execute(RegisterUserCommand $command): UserResponse
    {
        $email = new Email($command->email);

        if ($this->repository->existsByEmail($email)) {
            throw new DomainException("Email '{$command->email}' is already registered.");
        }

        $user = User::register(
            name:     $command->name,
            email:    $email,
            password: HashedPassword::fromPlainText($command->password),
            role:     UserRole::CUSTOMER,
        );

        $this->repository->save($user);
        $this->dispatcher->dispatchAll($user->pullDomainEvents());

        return UserResponse::fromUser($user);
    }
}

<?php

declare(strict_types=1);

namespace Weale\Application\User;

use Firebase\JWT\JWT;
use Weale\Domain\Shared\Exceptions\NotFoundException;
use Weale\Domain\Shared\Exceptions\UnauthorizedException;
use Weale\Domain\User\UserRepositoryInterface;
use Weale\Domain\User\ValueObjects\Email;

final class LoginUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $repository,
    ) {}

    public function execute(LoginCommand $command): LoginResponse
    {
        $email = new Email($command->email);
        $user  = $this->repository->findByEmail($email);

        if ($user === null) {
            throw new UnauthorizedException('Invalid credentials.');
        }

        if (!$user->isActive()) {
            throw new UnauthorizedException('User account is inactive.');
        }

        if (!$user->verifyPassword($command->password)) {
            throw new UnauthorizedException('Invalid credentials.');
        }

        $secret     = $_ENV['JWT_SECRET'] ?? 'changeme';
        $expiration = (int) ($_ENV['JWT_EXPIRATION'] ?? 3600);
        $issuedAt   = time();

        $payload = [
            'iss'  => $_ENV['APP_NAME'] ?? 'weale-store',
            'iat'  => $issuedAt,
            'exp'  => $issuedAt + $expiration,
            'sub'  => $user->id()->value(),
            'role' => $user->role()->value,
        ];

        $token = JWT::encode($payload, $secret, 'HS256');

        return new LoginResponse(
            token:     $token,
            expiresIn: $expiration,
            user:      UserResponse::fromUser($user),
        );
    }
}

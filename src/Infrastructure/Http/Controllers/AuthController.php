<?php

declare(strict_types=1);

namespace Weale\Infrastructure\Http\Controllers;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Weale\Application\User\LoginCommand;
use Weale\Application\User\LoginUseCase;
use Weale\Application\User\RegisterUserCommand;
use Weale\Application\User\RegisterUserUseCase;
use Weale\Domain\Shared\Exceptions\DomainException;
use Weale\Domain\Shared\Exceptions\UnauthorizedException;

final class AuthController
{
    public function __construct(
        private readonly RegisterUserUseCase $registerUser,
        private readonly LoginUseCase        $login,
    ) {}

    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = (array) $request->getParsedBody();

        try {
            $command = new RegisterUserCommand(
                name:     (string) ($body['name']     ?? ''),
                email:    (string) ($body['email']    ?? ''),
                password: (string) ($body['password'] ?? ''),
            );

            $user = $this->registerUser->execute($command);
            return $this->json($response, ['data' => $user->toArray()], 201);
        } catch (DomainException | InvalidArgumentException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 422);
        }
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = (array) $request->getParsedBody();

        try {
            $command  = new LoginCommand(
                email:    (string) ($body['email']    ?? ''),
                password: (string) ($body['password'] ?? ''),
            );
            $loginRes = $this->login->execute($command);
            return $this->json($response, $loginRes->toArray());
        } catch (UnauthorizedException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 401);
        } catch (InvalidArgumentException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 422);
        }
    }

    private function json(ResponseInterface $response, mixed $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}

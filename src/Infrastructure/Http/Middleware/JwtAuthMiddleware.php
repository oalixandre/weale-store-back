<?php

declare(strict_types=1);

namespace Weale\Infrastructure\Http\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

final class JwtAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorized('Missing or invalid Authorization header.');
        }

        $token  = substr($authHeader, 7);
        $secret = $_ENV['JWT_SECRET'] ?? 'changeme';

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            $payload = (array) $decoded;
            $request = $request->withAttribute('token', $payload);
            $request = $request->withAttribute('user_id', $payload['sub'] ?? null);
            $request = $request->withAttribute('user_role', $payload['role'] ?? null);
        } catch (Throwable) {
            return $this->unauthorized('Invalid or expired token.');
        }

        return $handler->handle($request);
    }

    private function unauthorized(string $message): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(401);
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}

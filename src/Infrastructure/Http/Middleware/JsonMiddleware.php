<?php

declare(strict_types=1);

namespace Weale\Infrastructure\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class JsonMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $contentType = $request->getHeaderLine('Content-Type');

        if (str_contains($contentType, 'application/json')) {
            $body    = (string) $request->getBody();
            $parsed  = json_decode($body, true);
            $request = $request->withParsedBody($parsed ?? []);
        }

        $response = $handler->handle($request);

        return $response->withHeader('X-Powered-By', 'Weale Store API');
    }
}

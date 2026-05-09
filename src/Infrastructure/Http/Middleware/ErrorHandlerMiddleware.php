<?php

declare(strict_types=1);

namespace Weale\Infrastructure\Http\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Interfaces\ErrorHandlerInterface;
use Throwable;

final class ErrorHandlerMiddleware implements ErrorHandlerInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
    ) {}

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
    ): ResponseInterface {
        $statusCode = 500;
        $message    = 'Internal Server Error';

        if ($exception instanceof HttpNotFoundException) {
            $statusCode = 404;
            $message    = 'Route not found';
        } elseif ($exception instanceof HttpMethodNotAllowedException) {
            $statusCode = 405;
            $message    = 'Method not allowed';
        }

        $payload = ['error' => $message];

        if ($displayErrorDetails) {
            $payload['details'] = [
                'type'    => get_class($exception),
                'message' => $exception->getMessage(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
            ];
        }

        $response = $this->responseFactory->createResponse($statusCode);
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json');
    }
}

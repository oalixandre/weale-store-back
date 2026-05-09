<?php

declare(strict_types=1);

use Slim\App;
use Slim\Middleware\ContentLengthMiddleware;
use Weale\Infrastructure\Http\Middleware\JsonMiddleware;
use Weale\Infrastructure\Http\Middleware\ErrorHandlerMiddleware;

return function (App $app): void {
    $app->addRoutingMiddleware();
    $app->addBodyParsingMiddleware();
    $app->add(new JsonMiddleware());

    $errorMiddleware = $app->addErrorMiddleware(
        displayErrorDetails: ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
        logErrors: true,
        logErrorDetails: true,
    );

    $errorMiddleware->setDefaultErrorHandler(
        new ErrorHandlerMiddleware($app->getResponseFactory())
    );
};

<?php

declare(strict_types=1);

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Weale\Infrastructure\Http\Controllers\AuthController;
use Weale\Infrastructure\Http\Controllers\OrderController;
use Weale\Infrastructure\Http\Controllers\ProductController;
use Weale\Infrastructure\Http\Middleware\JwtAuthMiddleware;

return function (App $app): void {

    // Health check
    $app->get('/health', function ($request, $response) {
        $response->getBody()->write(json_encode([
            'status' => 'ok',
            'app'    => $_ENV['APP_NAME'] ?? 'weale-store',
            'env'    => $_ENV['APP_ENV'] ?? 'unknown',
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // API v1
    $app->group('/api/v1', function (RouteCollectorProxy $group) {

        // Auth (public)
        $group->post('/auth/register', [AuthController::class, 'register']);
        $group->post('/auth/login',    [AuthController::class, 'login']);

        // Products (public read, protected write)
        $group->group('/products', function (RouteCollectorProxy $g) {
            $g->get('',      [ProductController::class, 'index']);
            $g->get('/{id}', [ProductController::class, 'show']);

            $g->post('',        [ProductController::class, 'store'])->add(JwtAuthMiddleware::class);
            $g->put('/{id}',    [ProductController::class, 'update'])->add(JwtAuthMiddleware::class);
            $g->delete('/{id}', [ProductController::class, 'destroy'])->add(JwtAuthMiddleware::class);
        });

        // Orders (all protected)
        $group->group('/orders', function (RouteCollectorProxy $g) {
            $g->get('',               [OrderController::class, 'index']);
            $g->post('',              [OrderController::class, 'store']);
            $g->get('/{id}',          [OrderController::class, 'show']);
            $g->patch('/{id}/status', [OrderController::class, 'updateStatus']);
        })->add(JwtAuthMiddleware::class);

    });
};

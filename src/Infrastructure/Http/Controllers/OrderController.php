<?php

declare(strict_types=1);

namespace Weale\Infrastructure\Http\Controllers;

use DomainException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Weale\Application\Order\GetOrderUseCase;
use Weale\Application\Order\PlaceOrderCommand;
use Weale\Application\Order\PlaceOrderUseCase;
use Weale\Application\Order\UpdateOrderStatusUseCase;
use Weale\Domain\Order\OrderRepositoryInterface;
use Weale\Domain\Shared\Exceptions\NotFoundException;
use Weale\Domain\User\ValueObjects\UserId;

final class OrderController
{
    public function __construct(
        private readonly PlaceOrderUseCase        $placeOrder,
        private readonly GetOrderUseCase          $getOrder,
        private readonly UpdateOrderStatusUseCase $updateStatus,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {}

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body   = (array) $request->getParsedBody();
        $userId = $this->extractUserId($request);

        try {
            $command = new PlaceOrderCommand(
                userId: $userId,
                items:  $body['items'] ?? [],
            );
            $order = $this->placeOrder->execute($command);
            return $this->json($response, ['data' => $order->toArray()], 201);
        } catch (NotFoundException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 404);
        } catch (DomainException | InvalidArgumentException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 422);
        }
    }

    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $order = $this->getOrder->execute($args['id']);
            return $this->json($response, ['data' => $order->toArray()]);
        } catch (NotFoundException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 404);
        }
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params  = $request->getQueryParams();
        $page    = max(1, (int) ($params['page']     ?? 1));
        $perPage = min(100, max(1, (int) ($params['per_page'] ?? 20)));

        $orders = $this->orderRepository->findAll($page, $perPage);
        $total  = $this->orderRepository->count();

        return $this->json($response, [
            'data' => array_map(fn ($o) => \Weale\Application\Order\OrderResponse::fromOrder($o)->toArray(), $orders),
            'meta' => [
                'total'    => $total,
                'page'     => $page,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function updateStatus(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = (array) $request->getParsedBody();

        try {
            $order = $this->updateStatus->execute($args['id'], (string) ($body['status'] ?? ''));
            return $this->json($response, ['data' => $order->toArray()]);
        } catch (NotFoundException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 404);
        } catch (DomainException | InvalidArgumentException $e) {
            return $this->json($response, ['error' => $e->getMessage()], 422);
        }
    }

    private function extractUserId(ServerRequestInterface $request): string
    {
        // JWT middleware injects decoded token as 'token' attribute
        $token = $request->getAttribute('token');
        return $token['sub'] ?? '';
    }

    private function json(ResponseInterface $response, mixed $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}

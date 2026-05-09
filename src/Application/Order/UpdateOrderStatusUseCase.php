<?php

declare(strict_types=1);

namespace Weale\Application\Order;

use InvalidArgumentException;
use Weale\Domain\Order\OrderRepositoryInterface;
use Weale\Domain\Order\ValueObjects\OrderId;
use Weale\Domain\Order\ValueObjects\OrderStatus;
use Weale\Domain\Shared\Exceptions\NotFoundException;

final class UpdateOrderStatusUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $repository,
    ) {}

    public function execute(string $id, string $status): OrderResponse
    {
        $newStatus = OrderStatus::tryFrom($status);

        if ($newStatus === null) {
            throw new InvalidArgumentException("Invalid order status: '{$status}'.");
        }

        $order = $this->repository->findById(OrderId::fromString($id));

        if ($order === null) {
            throw new NotFoundException("Order not found: {$id}");
        }

        $order->transitionTo($newStatus);
        $this->repository->save($order);

        return OrderResponse::fromOrder($order);
    }
}

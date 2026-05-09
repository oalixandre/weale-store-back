<?php

declare(strict_types=1);

namespace Weale\Application\Order;

use Weale\Domain\Order\OrderRepositoryInterface;
use Weale\Domain\Order\ValueObjects\OrderId;
use Weale\Domain\Shared\Exceptions\NotFoundException;

final class GetOrderUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $repository,
    ) {}

    public function execute(string $id): OrderResponse
    {
        $order = $this->repository->findById(OrderId::fromString($id));

        if ($order === null) {
            throw new NotFoundException("Order not found: {$id}");
        }

        return OrderResponse::fromOrder($order);
    }
}

<?php

declare(strict_types=1);

namespace Weale\Domain\Order;

use Weale\Domain\Order\ValueObjects\OrderId;
use Weale\Domain\User\ValueObjects\UserId;

interface OrderRepositoryInterface
{
    public function findById(OrderId $id): ?Order;

    /** @return Order[] */
    public function findByUser(UserId $userId, int $page = 1, int $perPage = 20): array;

    /** @return Order[] */
    public function findAll(int $page = 1, int $perPage = 20): array;

    public function save(Order $order): void;
    public function count(): int;
    public function countByUser(UserId $userId): int;
}

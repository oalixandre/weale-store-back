<?php

declare(strict_types=1);

namespace Weale\Infrastructure\Persistence\Repositories;

use Doctrine\ORM\EntityManagerInterface;
use Weale\Domain\Order\Order;
use Weale\Domain\Order\OrderRepositoryInterface;
use Weale\Domain\Order\ValueObjects\OrderId;
use Weale\Domain\Order\ValueObjects\OrderItem;
use Weale\Domain\Order\ValueObjects\OrderStatus;
use Weale\Domain\Product\ValueObjects\Money;
use Weale\Domain\Product\ValueObjects\ProductId;
use Weale\Domain\User\ValueObjects\UserId;
use Weale\Infrastructure\Persistence\Entities\OrderEntity;

final class DoctrineOrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public function findById(OrderId $id): ?Order
    {
        $entity = $this->em->find(OrderEntity::class, $id->value());
        return $entity ? $this->toDomain($entity) : null;
    }

    public function findByUser(UserId $userId, int $page = 1, int $perPage = 20): array
    {
        $entities = $this->em->getRepository(OrderEntity::class)
            ->createQueryBuilder('o')
            ->where('o.userId = :userId')
            ->setParameter('userId', $userId->value())
            ->orderBy('o.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()->getResult();

        return array_map(fn ($e) => $this->toDomain($e), $entities);
    }

    public function findAll(int $page = 1, int $perPage = 20): array
    {
        $entities = $this->em->getRepository(OrderEntity::class)
            ->createQueryBuilder('o')
            ->orderBy('o.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()->getResult();

        return array_map(fn ($e) => $this->toDomain($e), $entities);
    }

    public function save(Order $order): void
    {
        $entity = $this->em->find(OrderEntity::class, $order->id()->value()) ?? new OrderEntity();
        $this->hydrateEntity($entity, $order);
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function count(): int
    {
        return (int) $this->em->getRepository(OrderEntity::class)
            ->createQueryBuilder('o')->select('COUNT(o.id)')
            ->getQuery()->getSingleScalarResult();
    }

    public function countByUser(UserId $userId): int
    {
        return (int) $this->em->getRepository(OrderEntity::class)
            ->createQueryBuilder('o')->select('COUNT(o.id)')
            ->where('o.userId = :userId')
            ->setParameter('userId', $userId->value())
            ->getQuery()->getSingleScalarResult();
    }

    private function toDomain(OrderEntity $e): Order
    {
        $items = array_map(fn (array $i) => OrderItem::create(
            productId:   ProductId::fromString($i['product_id']),
            productName: $i['product_name'],
            quantity:    $i['quantity'],
            unitPrice:   new Money((int) ($i['unit_price_cents']), $i['currency'] ?? 'BRL'),
        ), $e->items);

        return Order::reconstitute(
            id:        OrderId::fromString($e->id),
            userId:    UserId::fromString($e->userId),
            items:     $items,
            status:    OrderStatus::from($e->status),
            createdAt: $e->createdAt,
            updatedAt: $e->updatedAt,
        );
    }

    private function hydrateEntity(OrderEntity $e, Order $order): void
    {
        $e->id               = $order->id()->value();
        $e->userId           = $order->userId()->value();
        $e->status           = $order->status()->value;
        $e->totalAmountCents = $order->total()->amountInCents();
        $e->totalCurrency    = $order->total()->currency();
        $e->createdAt        = $order->createdAt();
        $e->updatedAt        = $order->updatedAt();
        $e->items            = array_map(fn (OrderItem $i) => [
            'product_id'        => $i->productId()->value(),
            'product_name'      => $i->productName(),
            'quantity'          => $i->quantity(),
            'unit_price_cents'  => $i->unitPrice()->amountInCents(),
            'currency'          => $i->unitPrice()->currency(),
        ], $order->items());
    }
}

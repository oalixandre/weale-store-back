<?php

declare(strict_types=1);

namespace Weale\Infrastructure\Persistence\Repositories;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Weale\Domain\Product\Product;
use Weale\Domain\Product\ProductRepositoryInterface;
use Weale\Domain\Product\ValueObjects\Money;
use Weale\Domain\Product\ValueObjects\ProductId;
use Weale\Infrastructure\Persistence\Entities\ProductEntity;

final class DoctrineProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public function findById(ProductId $id): ?Product
    {
        $entity = $this->em->find(ProductEntity::class, $id->value());
        return $entity ? $this->toDomain($entity) : null;
    }

    public function findAll(int $page = 1, int $perPage = 20): array
    {
        $offset   = ($page - 1) * $perPage;
        $entities = $this->em->getRepository(ProductEntity::class)
            ->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        return array_map(fn ($e) => $this->toDomain($e), $entities);
    }

    public function findByCategory(string $category): array
    {
        $entities = $this->em->getRepository(ProductEntity::class)
            ->findBy(['category' => $category]);

        return array_map(fn ($e) => $this->toDomain($e), $entities);
    }

    public function save(Product $product): void
    {
        $entity = $this->em->find(ProductEntity::class, $product->id()->value());

        if ($entity === null) {
            $entity = new ProductEntity();
        }

        $this->hydrateEntity($entity, $product);
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function delete(ProductId $id): void
    {
        $entity = $this->em->find(ProductEntity::class, $id->value());
        if ($entity !== null) {
            $this->em->remove($entity);
            $this->em->flush();
        }
    }

    public function count(): int
    {
        return (int) $this->em->getRepository(ProductEntity::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function toDomain(ProductEntity $entity): Product
    {
        return Product::reconstitute(
            id:          ProductId::fromString($entity->id),
            name:        $entity->name,
            description: $entity->description,
            price:       new Money($entity->priceAmountCents, $entity->priceCurrency),
            stock:       $entity->stock,
            category:    $entity->category,
            active:      $entity->active,
            createdAt:   $entity->createdAt,
            updatedAt:   $entity->updatedAt,
        );
    }

    private function hydrateEntity(ProductEntity $entity, Product $product): void
    {
        $entity->id               = $product->id()->value();
        $entity->name             = $product->name();
        $entity->description      = $product->description();
        $entity->priceAmountCents = $product->price()->amountInCents();
        $entity->priceCurrency    = $product->price()->currency();
        $entity->stock            = $product->stock();
        $entity->category         = $product->category();
        $entity->active           = $product->isActive();
        $entity->createdAt        = $product->createdAt();
        $entity->updatedAt        = $product->updatedAt();
    }
}

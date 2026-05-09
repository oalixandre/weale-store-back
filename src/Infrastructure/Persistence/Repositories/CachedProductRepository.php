<?php

declare(strict_types=1);

namespace Weale\Infrastructure\Persistence\Repositories;

use Weale\Domain\Product\Product;
use Weale\Domain\Product\ProductRepositoryInterface;
use Weale\Domain\Product\ValueObjects\ProductId;
use Weale\Infrastructure\Cache\CacheInterface;

final class CachedProductRepository implements ProductRepositoryInterface
{
    private const TTL         = 300; // 5 min
    private const KEY_PREFIX  = 'product:';
    private const KEY_LIST    = 'products:list:';
    private const KEY_COUNT   = 'products:count';

    public function __construct(
        private readonly ProductRepositoryInterface $inner,
        private readonly CacheInterface            $cache,
    ) {}

    public function findById(ProductId $id): ?Product
    {
        $key    = self::KEY_PREFIX . $id->value();
        $cached = $this->cache->get($key);

        if ($cached !== null) {
            return $cached;
        }

        $product = $this->inner->findById($id);

        if ($product !== null) {
            $this->cache->set($key, $product, self::TTL);
        }

        return $product;
    }

    public function findAll(int $page = 1, int $perPage = 20): array
    {
        $key    = self::KEY_LIST . "{$page}:{$perPage}";
        $cached = $this->cache->get($key);

        if ($cached !== null) {
            return $cached;
        }

        $products = $this->inner->findAll($page, $perPage);
        $this->cache->set($key, $products, self::TTL);

        return $products;
    }

    public function findByCategory(string $category): array
    {
        return $this->inner->findByCategory($category);
    }

    public function save(Product $product): void
    {
        $this->inner->save($product);
        $this->invalidate($product->id());
    }

    public function delete(ProductId $id): void
    {
        $this->inner->delete($id);
        $this->invalidate($id);
    }

    public function count(): int
    {
        $cached = $this->cache->get(self::KEY_COUNT);

        if ($cached !== null) {
            return (int) $cached;
        }

        $count = $this->inner->count();
        $this->cache->set(self::KEY_COUNT, $count, self::TTL);

        return $count;
    }

    private function invalidate(ProductId $id): void
    {
        $this->cache->delete(self::KEY_PREFIX . $id->value());
        $this->cache->delete(self::KEY_COUNT);
        // Invalidate list pages (brute-force prefix pattern)
        for ($p = 1; $p <= 10; $p++) {
            foreach ([10, 20, 50, 100] as $pp) {
                $this->cache->delete(self::KEY_LIST . "{$p}:{$pp}");
            }
        }
    }
}

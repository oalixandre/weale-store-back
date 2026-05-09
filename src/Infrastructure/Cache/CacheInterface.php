<?php

declare(strict_types=1);

namespace Weale\Infrastructure\Cache;

interface CacheInterface
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttl = 3600): void;
    public function delete(string $key): void;
    public function has(string $key): bool;
    public function flush(): void;
}

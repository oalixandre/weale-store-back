<?php

declare(strict_types=1);

namespace Weale\Infrastructure\Cache;

final class NullCache implements CacheInterface
{
    public function get(string $key): mixed      { return null; }
    public function set(string $key, mixed $value, int $ttl = 3600): void {}
    public function delete(string $key): void    {}
    public function has(string $key): bool       { return false; }
    public function flush(): void                {}
}

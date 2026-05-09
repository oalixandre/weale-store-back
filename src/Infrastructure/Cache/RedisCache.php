<?php

declare(strict_types=1);

namespace Weale\Infrastructure\Cache;

use Redis;
use RuntimeException;

final class RedisCache implements CacheInterface
{
    private Redis $redis;

    public function __construct(
        string $host = 'redis',
        int    $port = 6379,
        string $prefix = 'weale:',
    ) {
        $this->redis = new Redis();

        if (!$this->redis->connect($host, $port)) {
            throw new RuntimeException("Could not connect to Redis at {$host}:{$port}");
        }

        $this->redis->setOption(Redis::OPT_PREFIX, $prefix);
        $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_JSON);
    }

    public function get(string $key): mixed
    {
        $value = $this->redis->get($key);
        return $value === false ? null : $value;
    }

    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        $this->redis->setEx($key, $ttl, $value);
    }

    public function delete(string $key): void
    {
        $this->redis->del($key);
    }

    public function has(string $key): bool
    {
        return (bool) $this->redis->exists($key);
    }

    public function flush(): void
    {
        $this->redis->flushDB();
    }
}

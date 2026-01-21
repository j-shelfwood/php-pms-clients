<?php

namespace Shelfwood\PhpPms\Http;

use Psr\SimpleCache\CacheInterface;
use Psr\Log\LoggerInterface;

class SharedRateLimiter
{
    public function __construct(
        private CacheInterface $cache,
        private ?LoggerInterface $logger = null,
        private string $prefix = 'pms:rate'
    ) {}

    public function throttle(string $key, int $maxRequests, int $windowSeconds, array $context = []): void
    {
        if ($maxRequests <= 0 || $windowSeconds <= 0) {
            return;
        }

        $bucket = "{$this->prefix}:{$key}";
        $windowKey = "{$bucket}:window";
        $countKey = "{$bucket}:count";
        $now = time();

        $windowStart = $this->cache->get($windowKey);
        $count = $this->cache->get($countKey);

        if (!is_int($windowStart) || !is_int($count) || ($now - $windowStart) >= $windowSeconds) {
            $windowStart = $now;
            $count = 0;
        }

        $count++;
        $this->cache->set($windowKey, $windowStart, $windowSeconds);
        $this->cache->set($countKey, $count, $windowSeconds);

        if ($count > $maxRequests) {
            $sleepFor = max(1, $windowSeconds - ($now - $windowStart));
            $this->logger?->warning('PMS API throttling', array_merge($context, [
                'key' => $key,
                'sleep_seconds' => $sleepFor,
                'limit' => $maxRequests,
                'window_seconds' => $windowSeconds,
            ]));
            sleep($sleepFor);
            $windowStart = time();
            $this->cache->set($windowKey, $windowStart, $windowSeconds);
            $this->cache->set($countKey, 1, $windowSeconds);
        }
    }
}

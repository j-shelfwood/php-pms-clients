<?php

namespace Tests\Support;

use DateInterval;
use DateTimeImmutable;
use Psr\SimpleCache\CacheInterface;

class InMemoryCache implements CacheInterface
{
    private array $values = [];
    private array $expirations = [];

    public function get($key, $default = null): mixed
    {
        if (!array_key_exists($key, $this->values)) {
            return $default;
        }

        if ($this->isExpired($key)) {
            $this->delete($key);
            return $default;
        }

        return $this->values[$key];
    }

    public function set($key, $value, $ttl = null): bool
    {
        $this->values[$key] = $value;
        $this->expirations[$key] = $this->normalizeTtl($ttl);
        return true;
    }

    public function delete($key): bool
    {
        unset($this->values[$key], $this->expirations[$key]);
        return true;
    }

    public function clear(): bool
    {
        $this->values = [];
        $this->expirations = [];
        return true;
    }

    public function getMultiple($keys, $default = null): iterable
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }
        return $results;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }

    public function deleteMultiple($keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->values) && !$this->isExpired($key);
    }

    private function isExpired(string $key): bool
    {
        $expiresAt = $this->expirations[$key] ?? null;
        if ($expiresAt === null) {
            return false;
        }

        return time() >= $expiresAt;
    }

    private function normalizeTtl(null|int|DateInterval $ttl): ?int
    {
        if ($ttl === null) {
            return null;
        }

        if ($ttl instanceof DateInterval) {
            $ttl = (new DateTimeImmutable())->add($ttl)->getTimestamp() - time();
        }

        if (!is_int($ttl)) {
            return null;
        }

        return time() + $ttl;
    }
}

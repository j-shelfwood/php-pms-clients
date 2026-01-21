<?php

namespace Shelfwood\PhpPms\Mews\Config;

class MewsConfig
{
    public function __construct(
        public readonly string $clientToken,
        public readonly string $accessToken,
        public readonly string $baseUrl = 'https://api.mews.com',
        public readonly string $clientName = 'MewsClient/1.0',
        public readonly ?string $webhookSecret = null,
        public readonly bool $rateLimitEnabled = true,
        public readonly int $rateLimitMaxRequests = 180,
        public readonly int $rateLimitWindowSeconds = 30,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->clientToken)) {
            throw new \InvalidArgumentException('Client token is required');
        }

        if (empty($this->accessToken)) {
            throw new \InvalidArgumentException('Access token is required');
        }

        if (!filter_var($this->baseUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid base URL format');
        }

        if ($this->rateLimitEnabled) {
            if ($this->rateLimitMaxRequests <= 0) {
                throw new \InvalidArgumentException('Rate limit max requests must be positive');
            }

            if ($this->rateLimitWindowSeconds <= 0) {
                throw new \InvalidArgumentException('Rate limit window seconds must be positive');
            }
        }
    }

    /**
     * Create configuration from array
     */
    public static function fromArray(array $config): self
    {
        $rateLimitEnabled = $config['rate_limit_enabled'] ?? true;
        if (is_string($rateLimitEnabled)) {
            $rateLimitEnabled = filter_var($rateLimitEnabled, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $rateLimitEnabled = $rateLimitEnabled ?? true;
        }

        return new self(
            clientToken: $config['client_token'] ?? throw new \InvalidArgumentException('client_token required'),
            accessToken: $config['access_token'] ?? throw new \InvalidArgumentException('access_token required'),
            baseUrl: $config['base_url'] ?? 'https://api.mews.com',
            clientName: $config['client_name'] ?? 'MewsClient/1.0',
            webhookSecret: $config['webhook_secret'] ?? null,
            rateLimitEnabled: (bool) $rateLimitEnabled,
            rateLimitMaxRequests: (int) ($config['rate_limit_max_requests'] ?? 180),
            rateLimitWindowSeconds: (int) ($config['rate_limit_window_seconds'] ?? 30),
        );
    }
}

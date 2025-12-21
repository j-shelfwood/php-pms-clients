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
    }

    /**
     * Create configuration from array
     */
    public static function fromArray(array $config): self
    {
        return new self(
            clientToken: $config['client_token'] ?? throw new \InvalidArgumentException('client_token required'),
            accessToken: $config['access_token'] ?? throw new \InvalidArgumentException('access_token required'),
            baseUrl: $config['base_url'] ?? 'https://api.mews.com',
            clientName: $config['client_name'] ?? 'MewsClient/1.0',
            webhookSecret: $config['webhook_secret'] ?? null,
        );
    }
}

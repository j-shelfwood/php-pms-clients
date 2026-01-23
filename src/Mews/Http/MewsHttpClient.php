<?php

namespace Shelfwood\PhpPms\Mews\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Shelfwood\PhpPms\Http\SharedRateLimiter;
use Shelfwood\PhpPms\Mews\Config\MewsConfig;
use Shelfwood\PhpPms\Mews\Exceptions\MewsApiException;
use Shelfwood\PhpPms\Exceptions\NetworkException;

class MewsHttpClient
{
    private ?array $configurationCache = null;
    private ?string $enterpriseTimezoneIdentifierCache = null;

    public function __construct(
        private MewsConfig $config,
        private Client $httpClient,
        private ?LoggerInterface $logger = null,
        private ?CacheInterface $cache = null
    ) {}

    /**
     * Make a POST request to the Mews API
     *
     * @param string $endpoint API endpoint path
     * @param array $body Request body
     * @return array Decoded JSON response
     * @throws MewsApiException For JSON decode errors
     * @throws NetworkException For HTTP/network errors
     */
    public function post(string $endpoint, array $body): array
    {
        $url = $this->config->baseUrl . $endpoint;

        try {
            $this->throttleRequest($endpoint);
            $this->logger?->debug('Mews API request', [
                'url' => $url,
                'body' => $body,
            ]);

            $response = $this->httpClient->post($url, [
                'json' => $body,
                'timeout' => 30,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = (string) $response->getBody();

            $this->logger?->debug('Mews API response', [
                'status_code' => $statusCode,
                'body' => $responseBody,
            ]);

            $data = json_decode($responseBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new MewsApiException(
                    'Failed to decode JSON response: ' . json_last_error_msg(),
                    $statusCode
                );
            }

            return $data;

        } catch (GuzzleException $e) {
            $statusCode = $e->getCode();
            $message = $e->getMessage();

            $this->logger?->error('Mews API error', [
                'url' => $url,
                'status_code' => $statusCode,
                'message' => $message,
                'exception' => get_class($e),
            ]);

            // Throw NetworkException to align with BookingManager adapter
            // and enable unified Retry-After header handling in application layer
            throw new NetworkException(
                "Mews API request failed: {$message}",
                $statusCode,
                $e
            );
        }
    }

    private function throttleRequest(string $endpoint): void
    {
        if (!$this->config->rateLimitEnabled) {
            return;
        }

        $maxRequests = $this->config->rateLimitMaxRequests;
        $windowSeconds = $this->config->rateLimitWindowSeconds;
        $rateKey = $this->config->getRateLimitKey('mews', $this->config->accessToken);

        if ($this->cache === null) {
            $this->logger?->warning('Mews API throttling disabled: shared cache not configured', [
                'endpoint' => $endpoint,
                'key' => $rateKey,
                'limit' => $maxRequests,
                'window_seconds' => $windowSeconds,
            ]);
            return;
        }

        (new SharedRateLimiter($this->cache, $this->logger))->throttle($rateKey, $maxRequests, $windowSeconds, [
            'endpoint' => $endpoint,
        ]);
    }

    /**
     * Build request body with authentication tokens
     *
     * @param array $additionalParams Additional request parameters
     * @return array Complete request body
     */
    public function buildRequestBody(array $additionalParams = []): array
    {
        $body = [
            'ClientToken' => $this->config->clientToken,
            'AccessToken' => $this->config->accessToken,
            'Client' => $this->config->clientName,
        ];

        // Remove null values from additional parameters
        $additionalParams = array_filter($additionalParams, function ($value) {
            return $value !== null;
        });

        return array_merge($body, $additionalParams);
    }

    /**
     * Get enterprise configuration (cached per client instance).
     *
     * @return array Decoded configuration response
     * @throws MewsApiException For JSON decode errors
     * @throws NetworkException For HTTP/network errors
     */
    public function getConfiguration(): array
    {
        if ($this->configurationCache !== null) {
            return $this->configurationCache;
        }

        $cacheKey = 'mews:configuration:' . hash('sha256', $this->config->clientToken);
        if ($this->cache && $this->cache->has($cacheKey)) {
            $cached = $this->cache->get($cacheKey);
            if (is_array($cached)) {
                $this->configurationCache = $cached;
                return $this->configurationCache;
            }
        }

        $body = $this->buildRequestBody([]);
        $this->configurationCache = $this->post('/api/connector/v1/configuration/get', $body);

        if ($this->cache) {
            $this->cache->set($cacheKey, $this->configurationCache, 3600);
        }

        return $this->configurationCache;
    }

    /**
     * Get enterprise timezone identifier (cached per client instance).
     *
     * @return string IANA timezone identifier (e.g., "Europe/Budapest")
     * @throws MewsApiException For JSON decode errors
     * @throws NetworkException For HTTP/network errors
     * @throws \RuntimeException If timezone missing from configuration
     */
    public function getEnterpriseTimezoneIdentifier(): string
    {
        if ($this->enterpriseTimezoneIdentifierCache !== null) {
            return $this->enterpriseTimezoneIdentifierCache;
        }

        $config = $this->getConfiguration();
        $timezone = $config['Enterprise']['TimeZoneIdentifier'] ?? null;

        if (!is_string($timezone) || $timezone === '') {
            throw new \RuntimeException('Enterprise timezone not found in configuration');
        }

        $this->enterpriseTimezoneIdentifierCache = $timezone;

        return $timezone;
    }
}

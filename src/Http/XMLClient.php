<?php

namespace Shelfwood\PhpPms\Http;

use GuzzleHttp\Client;
use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;
use GuzzleHttp\ClientInterface;
use Shelfwood\PhpPms\Http\XMLParser;
use Shelfwood\PhpPms\Exceptions\ParseException;
use Shelfwood\PhpPms\Exceptions\HttpClientException;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use Shelfwood\PhpPms\Exceptions\NetworkException;

abstract class XMLClient
{
    protected ClientInterface $httpClient;
    protected LoggerInterface $logger;
    protected string $baseUrl;
    protected string $apiKey;
    protected int $defaultTimeout = 30;
    protected array $defaultHeaders = ['Accept' => 'application/xml'];

    public function __construct(
        string $baseUrl,
        string $apiKey,
        ?ClientInterface $httpClient = null,
        ?LoggerInterface $logger = null
    ) {
        $this->baseUrl = rtrim($baseUrl, '/'); // Remove trailing slash to prevent double slashes
        $this->apiKey = $apiKey;
        $this->httpClient = $httpClient ?? new Client();
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Execute a POST request with form data, injects credentials, returns raw XML string.
     * Throws NetworkException on network/transport errors.
     */
    protected function executePostRequest(string $url, array $formData): string
    {
        $options = [
            'headers' => $this->defaultHeaders,
            'timeout' => $this->defaultTimeout,
            'form_params' => array_merge($formData, [
                'key' => $this->apiKey,
            ]),
        ];
        try {
            $response = $this->httpClient->request('POST', $url, $options);
            $body = $response->getBody()->getContents();
            if (empty($body)) {
                $this->logger->warning('Received empty response body from API.', [
                    'url' => $url,
                ]);
                throw new NetworkException('Received empty response body from API.');
            }
            return $body;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $this->logger->error('HTTP request failed', [
                'url' => $url,
                'error' => $e->getMessage(),
                'response_body' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null,
            ]);
            throw new NetworkException('HTTP request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}

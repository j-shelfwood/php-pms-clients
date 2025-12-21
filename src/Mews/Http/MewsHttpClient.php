<?php

namespace Shelfwood\PhpPms\Mews\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Shelfwood\PhpPms\Mews\Config\MewsConfig;
use Shelfwood\PhpPms\Mews\Exceptions\MewsApiException;

class MewsHttpClient
{
    public function __construct(
        private MewsConfig $config,
        private Client $httpClient,
        private ?LoggerInterface $logger = null
    ) {}

    /**
     * Make a POST request to the Mews API
     *
     * @param string $endpoint API endpoint path
     * @param array $body Request body
     * @return array Decoded JSON response
     * @throws MewsApiException
     */
    public function post(string $endpoint, array $body): array
    {
        $url = $this->config->baseUrl . $endpoint;

        try {
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

            throw new MewsApiException(
                "Mews API request failed: {$message}",
                $statusCode,
                $e
            );
        }
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
}

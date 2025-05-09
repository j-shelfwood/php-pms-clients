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

abstract class XMLClient
{
    protected ClientInterface $httpClient;
    protected LoggerInterface $logger;
    protected string $baseUrl;
    protected string $apiKey;
    protected string $username;
    protected int $defaultTimeout = 30;
    protected array $defaultHeaders = ['Accept' => 'application/xml'];

    public function __construct(
        string $baseUrl,
        string $apiKey,
        string $username,
        ?ClientInterface $httpClient = null,
        ?LoggerInterface $logger = null
    ) {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->username = $username;
        $this->httpClient = $httpClient ?? new Client();
        $this->logger = $logger ?? new NullLogger();
    }

    protected function sendRequest(string $method, string $url, array $options = []): array
    {
        $baseOptions = [
            'headers' => $this->defaultHeaders,
            'timeout' => $this->defaultTimeout,
        ];

        $finalOptions = array_merge($baseOptions, $options);
        if (!isset($finalOptions['form_params']) || !is_array($finalOptions['form_params'])) {
            if (isset($finalOptions['form_params'])) {
                 $this->logger->warning('form_params was provided but not as an array, resetting to empty array before adding credentials.', [
                    'original_form_params' => $finalOptions['form_params']
                ]);
            }
            $finalOptions['form_params'] = [];
        }
        $finalOptions['form_params']['key'] = $this->apiKey;
        $finalOptions['form_params']['username'] = $this->username;

        $this->logger->debug('Sending request with final options', [
            'method' => $method,
            'url' => $url,
            'options' => $finalOptions,
        ]);

        try {
            $response = $this->httpClient->request($method, $url, $finalOptions);
            $body = $response->getBody()->getContents();
        } catch (GuzzleRequestException $e) {
            $this->logger->error('HTTP request failed', [
                'url' => $url,
                'method' => $method,
                'error' => $e->getMessage(),
                'response_body' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null,
            ]);
            throw new HttpClientException('HTTP request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }

        try {
            $parsedResponse = XMLParser::parse($body);
        } catch (ParseException $e) {
            $this->logger->error('Failed to parse XML response', [
                'url' => $url,
                'method' => $method,
                'error' => $e->getMessage(),
                'body' => $body,
            ]);
            throw new HttpClientException('Failed to parse XML response: ' . $e->getMessage(), 0, $e);
        }

        if (XMLParser::hasError($parsedResponse)) {
            $errorMessage = (string) ($parsedResponse->error->message ?? 'Unknown API error');
            $errorCode = isset($parsedResponse->error->code) ? (int) $parsedResponse->error->code : 0;
            $this->logger->error('API Error', [
                'url' => $url,
                'method' => $method,
                'errorCode' => $errorCode,
                'errorMessage' => $errorMessage,
                'response_body' => $body,
            ]);
            throw new HttpClientException("API Error: {$errorMessage}" . ($errorCode !== 0 ? " (Code: {$errorCode})" : ""), $errorCode);
        }

        return [$parsedResponse];
    }
}

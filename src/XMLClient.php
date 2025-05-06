<?php

namespace PhpPms\Clients; // Changed namespace

use Exception;
use SimpleXMLElement;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger; // Import NullLogger for default
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client; // Import Guzzle Client for default
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use PhpPms\Clients\Exceptions\HttpClientException;
use Tightenco\Collect\Support\Collection; // Corrected use statement

abstract class XMLClient
{
    protected ClientInterface $httpClient;
    protected LoggerInterface $logger;
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct(
        string $baseUrl,
        string $apiKey,
        ?ClientInterface $httpClient = null,
        ?LoggerInterface $logger = null
    ) {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->httpClient = $httpClient ?? new Client();
        $this->logger = $logger ?? new NullLogger();
    }

    abstract protected function sendRequest(string $xml): string;

    protected function makeRequest(string $endpoint, array $params = [], string $method = 'GET'): Collection
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
        $this->logger->debug('Making API request', [
            'method' => $method,
            'url' => $url,
            'params' => $params, // Be mindful of logging sensitive keys like api_key if necessary
        ]);

        $requestParams = $params;
        // Assuming apiKey should be part of query parameters for GET or form parameters for POST
        $requestParams['key'] = $this->apiKey;

        try {
            if ($method === 'GET') {
                $response = $this->httpClient->request($method, $url, ['query' => $requestParams]);
            } else {
                $response = $this->httpClient->request($method, $url, ['form_params' => $requestParams]);
            }
            $body = $response->getBody()->getContents();
        } catch (GuzzleRequestException $e) {
            $this->logger->error('HTTP request failed', [
                'url' => $url,
                'method' => $method,
                'error' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null,
            ]);
            throw new HttpClientException('HTTP request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }

        $parsedResponse = $this->parseXml($body);

        if ($this->hasError($parsedResponse)) {
            $this->logError($endpoint, $params, $parsedResponse); // params here are original, without apiKey
            throw new HttpClientException('Error in API response: '.json_encode($parsedResponse->toArray()));
        }

        return $parsedResponse;
    }

    protected function hasError(Collection $response): bool
    {
        // Adjusted to use str_contains if available, or fallback
        $responseText = $response->get('response', '');
        $hasResponseError = is_string($responseText) && str_contains($responseText, '<error code="');
        return $response->has('code') || $hasResponseError;
    }

    protected function logError(string $endpoint, array $params, Collection $response): void
    {
        $this->logger->error("Error while requesting {$endpoint}", [
            'params' => $params,
            'response' => $response->toArray(),
        ]);
    }

    protected function parseXml(string $xml): Collection
    {
        try {
            // Ensure XML is not empty before parsing to avoid warnings with SimpleXMLElement
            if (empty(trim($xml))) {
                throw new Exception('Cannot parse empty XML string.');
            }
            $element = new SimpleXMLElement($xml);
            $json = json_encode($element);
            if ($json === false) {
                // Consider custom exception: throw new ParseException('Failed to encode XML to JSON.');
                throw new Exception('Failed to encode XML to JSON.');
            }
            $array = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Consider custom exception: throw new ParseException('Failed to decode JSON to array: ' . json_last_error_msg());
                throw new Exception('Failed to decode JSON to array: ' . json_last_error_msg());
            }

            return new Collection($array);
        } catch (Exception $e) {
            // Ensure PhpPms\Clients\Exceptions\ParseException is defined if you use it.
            throw new Exception("Error parsing XML: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Build a standardized OTA error response XML.
     */
    protected function buildErrorResponse(string $rootElement, string $message, int $code, string $type = ''): string
    {
        $typeAttr = $type !== '' ? sprintf(' Type="%s"', htmlspecialchars($type, ENT_XML1)) : '';
        $escapedMessage = htmlspecialchars($message, ENT_XML1);
        $escapedRootElement = htmlspecialchars($rootElement, ENT_XML1);

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<{$escapedRootElement} Version="2.0" xmlns="http://www.opentravel.org/OTA/2003/05">
    <Errors>
        <Error Code="{$code}" ShortText="{$escapedMessage}"{$typeAttr}/>
    </Errors>
</{$escapedRootElement}>
XML;
    }
}

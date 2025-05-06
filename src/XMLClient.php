<?php

namespace Shelfwood\PhpPms\Clients;

use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use Shelfwood\PhpPms\Clients\Exceptions\HttpClientException;
use Shelfwood\PhpPms\Clients\Exceptions\ParseException; // Ensure this is used if thrown by XmlParser
use Shelfwood\PhpPms\Clients\Util\XmlParser; // Added
use Shelfwood\PhpPms\Clients\Util\Dtos\ErrorDetailsDto; // Added
use Tightenco\Collect\Support\Collection;

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

    protected function makeRequest(string $endpoint, array $params = [], string $method = 'GET'): Collection
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
        $this->logger->debug('Making API request', [
            'method' => $method,
            'url' => $url,
            'params' => array_diff_key($params, array_flip(['key', 'apiKey'])),
        ]);

        $requestParams = $params;
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
                'response_body' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null,
            ]);
            throw new HttpClientException('HTTP request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }

        try {
            $parsedResponse = XmlParser::parse($body);
        } catch (ParseException $e) {
            $this->logger->error('Failed to parse XML response', [
                'url' => $url,
                'method' => $method,
                'error' => $e->getMessage(),
                'body' => $body, // Log the body that failed to parse
            ]);
            throw new HttpClientException('Failed to parse XML response: ' . $e->getMessage(), 0, $e);
        }


        if (XmlParser::hasError($parsedResponse)) {
            $errorDetails = XmlParser::extractErrorDetails($parsedResponse);
            $this->logError($endpoint, $params, $errorDetails); // Pass DTO to logError

            $exceptionMessage = sprintf(
                'API Error%s: %s',
                ($errorDetails->code ? ' (Code: ' . $errorDetails->code . ')' : ''),
                $errorDetails->message ?? 'Error in API response'
            );

            $exceptionCode = 0;
            if ($errorDetails->code !== null) {
                 if (is_int($errorDetails->code)) {
                    $exceptionCode = $errorDetails->code;
                } elseif (ctype_digit((string)$errorDetails->code)) {
                    $exceptionCode = (int)$errorDetails->code;
                }
            }

            throw new HttpClientException($exceptionMessage, $exceptionCode);
        }

        return $parsedResponse;
    }

    protected function logError(string $endpoint, array $params, ErrorDetailsDto $errorDetails): void // Changed signature
    {
        $this->logger->error("Error while requesting {$endpoint}", [
            'params' => array_diff_key($params, array_flip(['key', 'apiKey'])),
            'api_error_code' => $errorDetails->code,
            'api_error_message' => $errorDetails->message,
            'api_response_fragment' => $errorDetails->rawResponseFragment,
        ]);
    }

    /**
     * Build a standardized OTA error response XML.
     * This method might be better placed in a utility class if used by multiple clients
     * or if the client itself is not supposed to generate XML.
     * For now, keeping it here if it's specific to this client's interaction patterns.
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

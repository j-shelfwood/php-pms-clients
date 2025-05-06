<?php

namespace PhpPms\Clients;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\HttpClientException;

abstract class XMLClient
{
    public function __construct(
        public readonly string $baseUrl,
        public readonly string $apiKey,
    ) {
        //
    }

    protected function makeRequest(string $endpoint, array $params = [], string $method = 'GET'): \Illuminate\Support\Collection
    {
        $url = "{$this->baseUrl}/{$endpoint}";
        Log::channel('sync')->debug('Making BookingManager API request', [
            'method' => $method,
            'url' => $url,
            'params' => $params, // Be mindful of logging sensitive keys like api_key if necessary
        ]);
        $params['key'] = $this->apiKey;
        $response = $method === 'GET'
            ? Http::get($url, $params)
            : Http::asForm()->post($url, $params);

        $body = $response->body();
        $parsedResponse = $this->parseXml($body);

        if ($this->hasError($parsedResponse)) {
            $this->logError($endpoint, $params, $parsedResponse);
            throw new HttpClientException('Error in response: '.json_encode($parsedResponse));
        }

        return $parsedResponse;
    }

    protected function hasError(\Illuminate\Support\Collection $response): bool
    {
        return $response->has('code') || str()->of($response->get('response', ''))->contains('<error code="');
    }

    protected function logError(string $endpoint, array $params, \Illuminate\Support\Collection $response): void
    {
        Log::error("Error while requesting {$endpoint} from bookingmanager", [
            'params' => $params,
            'response' => $response,
        ]);
    }

    protected function parseXml(string $body): Collection
    {
        // Remove default namespace to simplify parsing
        $cleanBody = preg_replace('/xmlns="[^"]+"/', '', $body, 1);
        $xml = simplexml_load_string($cleanBody, 'SimpleXMLElement', LIBXML_NOCDATA);
        $data = $this->xmlToArray($xml);

        return collect($data);
    }

    protected function xmlToArray($xml): array
    {
        $result = [];

        // Handle attributes of the root element
        foreach ($xml->attributes() as $attrName => $attrValue) {
            $result[$attrName] = (string) $attrValue;
        }

        foreach ($xml->children() as $child) {
            $childName = $child->getName();

            // Handle attributes
            $attributes = [];
            foreach ($child->attributes() as $attrName => $attrValue) {
                $attributes[$attrName] = (string) $attrValue;
            }

            // Handle child elements
            $childData = $this->xmlToArray($child);

            // Merge attributes and child data
            $data = array_merge($attributes, $childData);

            // Handle text content
            $text = trim((string) $child);
            if (! empty($text)) {
                $data['#text'] = $text;
            }

            // If there's only one child element, don't wrap it in an array
            if (count($data) === 0) {
                $data = $text;
            } elseif (count($data) === 1 && isset($data['#text'])) {
                $data = $data['#text'];
            }

            // Add to result
            if (! isset($result[$childName])) {
                $result[$childName] = $data;
            } else {
                if (! is_array($result[$childName]) || ! isset($result[$childName][0])) {
                    $result[$childName] = [$result[$childName]];
                }
                $result[$childName][] = $data;
            }
        }

        return $result;
    }

    /**
     * Build a standardized OTA error response XML.
     */
    protected function buildErrorResponse(string $rootElement, string $message, int $code, string $type = ''): string
    {
        $typeAttr = $type !== '' ? " Type=\"{$type}\"" : '';

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<{$rootElement} Version="2.0" xmlns="http://www.opentravel.org/OTA/2003/05">
    <Errors>
        <Error Code="{$code}" ShortText="{$message}"{$typeAttr}/>
    </Errors>
</{$rootElement}>
XML;
    }
}

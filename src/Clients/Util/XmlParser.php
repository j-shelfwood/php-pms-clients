<?php

namespace Shelfwood\PhpPms\Clients\Util;

use Shelfwood\PhpPms\Clients\Util\Dtos\ErrorDetailsDto;
use Shelfwood\PhpPms\Clients\Exceptions\ParseException;
use Tightenco\Collect\Support\Collection;
use SimpleXMLElement;
use Exception;

class XmlParser
{
    /**
     * Parses an XML string into a Collection.
     *
     * @throws ParseException If XML parsing or conversion fails.
     */
    public static function parse(string $xml): Collection
    {
        try {
            if (empty(trim($xml))) {
                throw new ParseException('Cannot parse empty XML string.');
            }
            // LIBXML_NOCDATA merges CDATA as text, LIBXML_NOERROR suppresses errors, LIBXML_NOWARNING suppresses warnings.
            // Errors should be checked with libxml_get_errors() if needed, but SimpleXMLElement throws on failure.
            $element = new SimpleXMLElement($xml, LIBXML_NOCDATA | LIBXML_NOERROR | LIBXML_NOWARNING);
            $json = json_encode($element);
            if ($json === false) {
                throw new ParseException('Failed to encode XML to JSON. Error: ' . json_last_error_msg());
            }
            $array = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ParseException('Failed to decode JSON to array: ' . json_last_error_msg());
            }
            return new Collection($array);
        } catch (Exception $e) { // Catches SimpleXMLElement's own exceptions for malformed XML
            if ($e instanceof ParseException) { // Re-throw if it's already our ParseException
                throw $e;
            }
            throw new ParseException("Error parsing XML: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Checks if the parsed XML response Collection contains an error.
     */
    public static function hasError(Collection $response): bool
    {
        // Check for OTA-style errors <Errors><Error Code="..." .../></Errors>
        $otaErrorCollection = $response->get('Errors.Error');
        if ($otaErrorCollection) {
            $otaErrorItem = $response->collect('Errors.Error')->first();
            if (is_array($otaErrorItem) && isset($otaErrorItem['@attributes']['Code'])) {
                return true;
            }
             if (is_array($otaErrorCollection) && isset($otaErrorCollection['@attributes']['Code'])) {
                 return true;
            }
        }

        // Check for <error code="...">...</error> or <Error code="...">...</Error>
        $directErrorData = $response->get('error') ?? $response->get('Error');
        if (is_array($directErrorData) && isset($directErrorData['@attributes']['code'])) {
            return true;
        }

        $rootAttributes = $response->get('@attributes');
        if (is_array($rootAttributes)) {
            $rootCode = $rootAttributes['code'] ?? $rootAttributes['Code'] ?? null;
            if ($rootCode !== null && $rootCode !== '0' && strtoupper($rootCode) !== 'OK' && !empty($rootCode)) {
                 return true;
            }
        }
        return false;
    }

    /**
     * Extracts error details from the parsed XML response Collection.
     */
    public static function extractErrorDetails(Collection $response): ErrorDetailsDto
    {
        $code = null;
        $message = 'Unknown API error';
        $rawFragment = [];

        $otaErrorCollection = $response->get('Errors.Error');
        if ($otaErrorCollection) {
            $errorItem = $response->collect('Errors.Error')->first();
             if (!is_array($errorItem) && is_array($otaErrorCollection) && isset($otaErrorCollection['@attributes'])) {
                $errorItem = $otaErrorCollection;
            }
            if (is_array($errorItem) && isset($errorItem['@attributes'])) {
                $code = $errorItem['@attributes']['Code'] ?? null;
                $message = $errorItem['@attributes']['ShortText'] ?? 'Error details not provided.';
                if (empty($message) && isset($errorItem[0]) && is_string($errorItem[0])) {
                    $message = trim($errorItem[0]);
                }
                $rawFragment = $errorItem;
                return new ErrorDetailsDto($code, $message, $rawFragment);
            }
        }

        $directErrorData = $response->get('error') ?? $response->get('Error');
        if (is_array($directErrorData) && isset($directErrorData['@attributes']['code'])) {
            $code = $directErrorData['@attributes']['code'];
            $msgContent = $directErrorData[0] ?? $directErrorData['message'] ?? $directErrorData['@attributes']['message'] ?? 'Error details not provided.';
            $message = is_string($msgContent) ? trim($msgContent) : json_encode($msgContent);
            $rawFragment = $directErrorData;
            return new ErrorDetailsDto($code, $message, $rawFragment);
        }

        $rootAttributes = $response->get('@attributes');
        if (is_array($rootAttributes)) {
            $rootCode = $rootAttributes['code'] ?? $rootAttributes['Code'] ?? null;
            if ($rootCode !== null && $rootCode !== '0' && strtoupper($rootCode) !== 'OK' && !empty($rootCode)) {
                $code = $rootCode;
                $messageContent = $rootAttributes['message'] ?? $rootAttributes['Message'] ?? $response->get('message', 'Error details not provided.');
                $message = is_string($messageContent) ? trim($messageContent) : json_encode($messageContent);
                $rawFragment = $rootAttributes;
                return new ErrorDetailsDto($code, $message, $rawFragment);
            }
        }
        return new ErrorDetailsDto($code, $message, $response->toArray()); // Fallback with full response as fragment
    }
}

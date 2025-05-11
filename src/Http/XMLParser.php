<?php

namespace Shelfwood\PhpPms\Http;

use Exception;
use SimpleXMLElement;
use Shelfwood\PhpPms\Exceptions\ErrorDetails;
use Shelfwood\PhpPms\Exceptions\ParseException;

class XmlParser
{
    /**
     * Parses an XML string into an array.
     *
     * @throws ParseException If XML parsing or conversion fails.
     */
    public static function parse(string $xml): array
    {
        try {
            if (empty(trim($xml))) {
                throw new ParseException('Cannot parse empty XML string.');
            }
            $element = new SimpleXMLElement($xml, LIBXML_NOCDATA | LIBXML_NOERROR | LIBXML_NOWARNING);
            $json = json_encode($element);
            if ($json === false) {
                throw new ParseException('Failed to encode XML to JSON. Error: ' . json_last_error_msg());
            }
            $array = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ParseException('Failed to decode JSON to array: ' . json_last_error_msg());
            }
            return $array;
        } catch (Exception $e) {
            if ($e instanceof ParseException) {
                throw $e;
            }
            throw new ParseException("Error parsing XML: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Checks if the parsed XML response array contains an error.
     */
    public static function hasError(array $response): bool
    {
        $otaErrorCollection = $response['Errors']['Error'] ?? null;
        $otaErrorCollection = $response['Errors']['Error'] ?? null;
        if ($otaErrorCollection) {
            $otaErrorItem = is_array($otaErrorCollection) && isset($otaErrorCollection[0]) ? $otaErrorCollection[0] : $otaErrorCollection;
            if (is_array($otaErrorItem) && isset($otaErrorItem['@attributes']['Code'])) {
                return true;
            }
            if (is_array($otaErrorCollection) && isset($otaErrorCollection['@attributes']['Code'])) {
                return true;
            }
        }

        $rootError = $response['Error'] ?? null;
        $rootError = $response['Error'] ?? null;
        if (is_array($rootError) && isset($rootError['@attributes']['Code'])) {
            return true;
        }

        $directErrorData = $response['error'] ?? null;
        $directErrorData = $response['error'] ?? null;
        if (is_array($directErrorData) && isset($directErrorData['@attributes']['code'])) {
            return true;
        }

        $rootAttributes = $response['@attributes'] ?? null;
        if (is_array($rootAttributes)) {
            $rootCode = $rootAttributes['code'] ?? $rootAttributes['Code'] ?? null;
            if ($rootCode !== null && (string) $rootCode !== '0' && strtoupper((string) $rootCode) !== 'OK' && !empty($rootCode)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Extracts error details from the parsed XML response array.
     */
    public static function extractErrorDetails(array $response): ErrorDetails
    {
        $code = null;
        $message = 'Unknown API error';
        $rawFragment = [];

        $otaErrorCollection = $response['Errors']['Error'] ?? null;
        $otaErrorCollection = $response['Errors']['Error'] ?? null;
        if ($otaErrorCollection) {
            $errorItem = is_array($otaErrorCollection) && isset($otaErrorCollection[0]) ? $otaErrorCollection[0] : $otaErrorCollection;
            if (is_array($errorItem) && isset($errorItem['@attributes'])) {
                $code = (string) ($errorItem['@attributes']['Code'] ?? null);
                $message = $errorItem['@attributes']['ShortText'] ?? 'Error details not provided.';
                if (empty($message) && isset($errorItem[0]) && is_string($errorItem[0])) {
                    $message = trim($errorItem[0]);
                }
                $rawFragment = $errorItem;
                return new ErrorDetails($code, $message, $rawFragment);
            }
        }

        $rootError = $response['Error'] ?? null;
        $rootError = $response['Error'] ?? null;
        if (is_array($rootError) && isset($rootError['@attributes']['Code'])) {
            $code = (string) ($rootError['@attributes']['Code'] ?? null);
            $message = $rootError['@attributes']['ShortText'] ?? 'Error details not provided.';
            if (empty($message) && isset($rootError[0]) && is_string($rootError[0])) {
                $message = trim($rootError[0]);
            }
            $rawFragment = $rootError;
            return new ErrorDetails($code, $message, $rawFragment);
        }

        $directErrorData = $response['error'] ?? null;
        $directErrorData = $response['error'] ?? null;
        if (is_array($directErrorData) && isset($directErrorData['@attributes']['code'])) {
            $code = (string) ($directErrorData['@attributes']['code'] ?? null);
            $msgContent = $directErrorData[0] ?? $directErrorData['message'] ?? $directErrorData['@attributes']['message'] ?? 'Error details not provided.';
            $message = is_string($msgContent) ? trim($msgContent) : json_encode($msgContent);
            $rawFragment = $directErrorData;
            return new ErrorDetails($code, $message, $rawFragment);
        }

        $rootAttributes = $response['@attributes'] ?? null;
        if (is_array($rootAttributes)) {
            $rootCode = $rootAttributes['code'] ?? $rootAttributes['Code'] ?? null;
            if ($rootCode !== null && (string) $rootCode !== '0' && strtoupper((string) $rootCode) !== 'OK' && !empty($rootCode)) {
                $code = (string) $rootCode;
                $messageContent = $rootAttributes['message'] ?? $rootAttributes['Message'] ?? ($response['message'] ?? 'Error details not provided.');
                $message = is_string($messageContent) ? trim($messageContent) : json_encode($messageContent);
                $rawFragment = $rootAttributes;
                return new ErrorDetails($code, $message, $rawFragment);
            }
        }
        return new ErrorDetails($code, $message, $response);
    }

    /**
     * Safely gets a string value from the data array.
     */
    public static function getString(array $data, string $key, ?string $default = null): ?string
    {
        $value = $data[$key] ?? $default;
        if (is_array($value) && empty($value)) {
            return $default;
        }
        if (!is_scalar($value) && $value !== null) {
            return $default;
        }
        return $value === null ? null : (string) $value;
    }

    /**
     * Safely gets an integer value from the data array.
     */
    public static function getInt(array $data, string $key, int $default = 0): int
    {
        $value = $data[$key] ?? $default;
        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * Safely gets a float value from the data array.
     */
    public static function getFloat(array $data, string $key, float $default = 0.0): float
    {
        $value = $data[$key] ?? $default;
        return is_numeric($value) ? (float) $value : $default;
    }

    /**
     * Safely gets a boolean value from the data array.
     */
    public static function getBool(array $data, string $key, bool $default = false): bool
    {
        $value = $data[$key] ?? $default;
        return is_bool($value) ? $value : (bool) $default;
    }
}

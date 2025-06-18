<?php

namespace Shelfwood\PhpPms\Http;

use Exception;
use SimpleXMLElement;
use Shelfwood\PhpPms\Exceptions\ErrorDetails;
use Shelfwood\PhpPms\Exceptions\XmlParsingException;

class XMLParser
{
    /**
     * Parses an XML string into an array.
     *
     * @throws XmlParsingException If XML parsing or conversion fails.
     */
    public static function parse(string $xml): array
    {
        try {
            if (empty(trim($xml))) {
                throw new XmlParsingException('Cannot parse empty XML string.');
            }
            $element = new SimpleXMLElement($xml, LIBXML_NOCDATA | LIBXML_NOERROR | LIBXML_NOWARNING);
            $array = self::xmlToArray($element);
            return $array;
        } catch (Exception $e) {
            if ($e instanceof XmlParsingException) {
                throw $e;
            }
            throw new XmlParsingException("Error parsing XML: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Converts SimpleXMLElement to array while preserving attributes even when elements have text content.
     *
     * @return array|string
     */
    private static function xmlToArray(SimpleXMLElement $element)
    {
        $result = [];

        // Handle attributes
        $attributes = [];
        foreach ($element->attributes() as $key => $value) {
            $attributes[$key] = (string) $value;
        }

        // Handle children
        $children = [];
        foreach ($element->children() as $key => $child) {
            $childArray = self::xmlToArray($child);
            if (isset($children[$key])) {
                // Multiple children with same tag name - convert to indexed array
                if (!is_array($children[$key]) || !isset($children[$key][0])) {
                    $children[$key] = [$children[$key]];
                }
                $children[$key][] = $childArray;
            } else {
                $children[$key] = $childArray;
            }
        }

        // Get text content
        $text = trim((string) $element);

        // Build result array
        if (!empty($attributes)) {
            $result['@attributes'] = $attributes;
        }

        if (!empty($children)) {
            $result = array_merge($result, $children);
        }

        if (!empty($text) && empty($children)) {
            // Element has only text content and possibly attributes
            if (!empty($attributes)) {
                $result['#text'] = $text;
            } else {
                // Element has only text content, return as string
                return $text;
            }
        } elseif (!empty($text) && !empty($children)) {
            // Element has both text and children - add text as #text
            $result['#text'] = $text;
        } elseif (empty($text) && empty($children) && !empty($attributes)) {
            // Element has only attributes, no text or children
            // Result already has @attributes set above
        } elseif (empty($text) && empty($children) && empty($attributes)) {
            // Empty element
            return '';
        }

        return $result;
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
        if (is_array($directErrorData)) {
            if (isset($directErrorData['@attributes']['code'])) {
                return true;
            }
            // BookingManager error structure: <error><code>...</code><message>...</message></error>
            if (isset($directErrorData['code']) && !empty($directErrorData['code'])) {
                return true;
            }
        }

        $rootAttributes = $response['@attributes'] ?? null;
        if (is_array($rootAttributes)) {
            $rootCode = $rootAttributes['code'] ?? $rootAttributes['Code'] ?? null;
            if ($rootCode !== null && (string) $rootCode !== '0' && strtoupper((string) $rootCode) !== 'OK' && !empty($rootCode)) {
                return true;
            }
        }

        // Check for BookingManager <e> error structure
        $bookingManagerError = $response['e'] ?? null;
        if (is_array($bookingManagerError) && isset($bookingManagerError['code']) && !empty($bookingManagerError['code'])) {
            return true;
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
        if (is_array($directErrorData)) {
            if (isset($directErrorData['@attributes']['code'])) {
                $code = (string) ($directErrorData['@attributes']['code'] ?? null);
                $msgContent = $directErrorData[0] ?? $directErrorData['message'] ?? $directErrorData['@attributes']['message'] ?? 'Error details not provided.';
                $message = is_string($msgContent) ? trim($msgContent) : json_encode($msgContent);
                $rawFragment = $directErrorData;
                return new ErrorDetails($code, $message, $rawFragment);
            }
            // Handle direct code and message children (e.g., BookingManager <error><code>...</code><message>...</message></error>)
            if (isset($directErrorData['code']) && !empty($directErrorData['code'])) {
                $code = (string) $directErrorData['code'];
                $message = (string) ($directErrorData['message'] ?? 'Error details not provided.');
                $rawFragment = $directErrorData;
                return new ErrorDetails($code, $message, $rawFragment);
            }
        }

        $rootAttributes = $response['@attributes'] ?? null;
        if (is_array($rootAttributes)) {
            $rootCode = $rootAttributes['code'] ?? $rootAttributes['Code'] ?? null;
            if ($rootCode !== null && (string) $rootCode !== '0' && strtoupper((string) $rootCode) !== 'OK' && !empty($rootCode)) {
                $code = (string) $rootCode;
                // Check for message in various locations: attributes, #text content, or child elements
                $messageContent = $rootAttributes['message'] ?? $rootAttributes['Message'] ??
                                ($response['#text'] ?? ($response['message'] ?? 'Error details not provided.'));
                $message = is_string($messageContent) ? trim($messageContent) : json_encode($messageContent);
                $rawFragment = $response; // Use entire response as fragment for root-level errors
                return new ErrorDetails($code, $message, $rawFragment);
            }
        }

        // Check for BookingManager <e> error structure
        $bookingManagerError = $response['e'] ?? null;
        if (is_array($bookingManagerError) && isset($bookingManagerError['code']) && !empty($bookingManagerError['code'])) {
            $code = (string) $bookingManagerError['code'];
            $message = (string) ($bookingManagerError['message'] ?? 'Error details not provided.');
            $rawFragment = $bookingManagerError;
            return new ErrorDetails($code, $message, $rawFragment);
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

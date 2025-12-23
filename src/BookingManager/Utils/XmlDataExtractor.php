<?php

namespace Shelfwood\PhpPms\BookingManager\Utils;

use Carbon\Carbon;

/**
 * XML Data Extraction Utilities
 *
 * Provides reusable helper methods for safely extracting data from XML-parsed arrays.
 * Handles common XML parser structures where elements with attributes become nested arrays.
 */
class XmlDataExtractor
{
    /**
     * Extract string value from XML data array
     *
     * Handles XML parser structure where elements with attributes become arrays with '#text' key.
     *
     * @param array<string, mixed> $data The XML data array
     * @param string $key The key to extract
     * @param string|null $default Default value if key not found
     * @return string|null
     */
    public static function getString(array $data, string $key, ?string $default = null): ?string
    {
        $value = $data[$key] ?? $default;

        // Handle new XML parser structure where elements with attributes become arrays
        if (is_array($value) && isset($value['#text'])) {
            $value = $value['#text'];
        }

        if (is_array($value) && empty($value)) {
            return $default;
        }

        if (!is_scalar($value) && $value !== null) {
            return $default;
        }

        return $value === null ? $default : (string) $value;
    }

    /**
     * Extract integer value from XML data array
     *
     * @param array<string, mixed> $data The XML data array
     * @param string $key The key to extract
     * @param int $default Default value if key not found or not numeric
     * @return int
     */
    public static function getInt(array $data, string $key, int $default = 0): int
    {
        $value = $data[$key] ?? $default;
        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * Extract Carbon date from XML data array
     *
     * Attempts to parse date from both main data array and attributes array.
     *
     * @param array<string, mixed> $data The XML data array
     * @param string $key The key to extract
     * @param array<string, mixed> $attributes Optional attributes array to check
     * @return Carbon|null
     */
    public static function getDate(array $data, string $key, array $attributes = []): ?Carbon
    {
        $value = $data[$key] ?? $attributes[$key] ?? null;

        if ($value === null || empty($value) || is_array($value)) {
            return null;
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Extract text content from elements that might have attributes
     *
     * Similar to getString but specifically for elements with attributes and text content.
     *
     * @param array<string, mixed> $data The XML data array
     * @param string $key The key to extract
     * @param string $default Default value if key not found
     * @return string
     */
    public static function getTextContent(array $data, string $key, string $default = ''): string
    {
        $value = $data[$key] ?? $default;

        // Handle array structure with attributes and text content
        if (is_array($value) && isset($value['#text'])) {
            return (string) $value['#text'];
        }

        // Handle simple string value
        if (is_string($value)) {
            return $value;
        }

        return $default;
    }
}

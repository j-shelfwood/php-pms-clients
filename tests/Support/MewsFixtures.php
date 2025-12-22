<?php

namespace Shelfwood\PhpPms\Tests\Support;

/**
 * Mews API Test Fixture Loader
 *
 * Provides centralized access to Mews API response fixtures and webhook payloads
 * for consistent testing across all consuming applications.
 *
 * Usage:
 * ```php
 * $payload = MewsFixtures::webhookPayload('service-order-updated');
 * $response = MewsFixtures::apiResponse('reservation-getbyid');
 * $signature = MewsFixtures::generateSignature($payload, 'secret');
 * ```
 */
class MewsFixtures
{
    /**
     * Load webhook payload fixture
     *
     * @param string $name Fixture name (without .json extension)
     * @return array Parsed webhook payload
     * @throws \RuntimeException If fixture file not found
     */
    public static function webhookPayload(string $name): array
    {
        $path = __DIR__ . '/../../mocks/mews/webhooks/' . $name . '.json';

        if (!file_exists($path)) {
            throw new \RuntimeException("Webhook fixture not found: {$name} at {$path}");
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid JSON in webhook fixture {$name}: " . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Load API response fixture
     *
     * @param string $name Fixture name (without .json extension)
     * @return array Parsed API response
     * @throws \RuntimeException If fixture file not found
     */
    public static function apiResponse(string $name): array
    {
        $path = __DIR__ . '/../../mocks/mews/responses/' . $name . '.json';

        if (!file_exists($path)) {
            throw new \RuntimeException("API response fixture not found: {$name} at {$path}");
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid JSON in response fixture {$name}: " . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Generate HMAC-SHA256 webhook signature
     *
     * Mews webhooks use HMAC-SHA256 for signature verification.
     * The signature is computed over the JSON-encoded payload.
     *
     * @param array $payload Webhook payload to sign
     * @param string $secret Webhook secret from Mews configuration
     * @return string HMAC-SHA256 signature (lowercase hex)
     */
    public static function generateSignature(array $payload, string $secret): string
    {
        return hash_hmac('sha256', json_encode($payload), $secret);
    }

    /**
     * Verify webhook signature
     *
     * @param array $payload Webhook payload
     * @param string $signature Signature from X-Mews-Signature header
     * @param string $secret Webhook secret
     * @return bool True if signature is valid
     */
    public static function verifySignature(array $payload, string $signature, string $secret): bool
    {
        $expected = self::generateSignature($payload, $secret);
        return hash_equals($expected, $signature);
    }

    // ========================================================================
    // TEST UUIDS - Consistent across all fixtures
    // ========================================================================

    /**
     * Enterprise UUID used across all fixtures
     */
    public const ENTERPRISE_ID = '851df8c8-90f2-4c4a-8e01-a4fc46b25178';

    /**
     * Integration UUID used in webhook payloads
     */
    public const INTEGRATION_ID = 'c8bee838-7fb1-4f4e-8fac-ac87008b2f90';

    /**
     * Service UUID (Accommodation service)
     */
    public const SERVICE_ID = 'bd26d8db-86a4-4f18-9e94-1b2362a1073c';

    /**
     * Resource UUID (Superior Apartment)
     */
    public const RESOURCE_ID = '095a6d7f-4893-4a3b-9c35-ff595d4bfa0c';

    /**
     * Resource Category UUID
     */
    public const CATEGORY_ID = '773d5e42-de1e-43a0-9ce6-c3e7511c1e0a';

    /**
     * Resource Category Assignment UUID
     */
    public const ASSIGNMENT_ID = 'abc12345-6789-0def-1234-567890abcdef';

    /**
     * Reservation UUID (used in webhooks and API responses)
     */
    public const RESERVATION_ID = 'bfee2c44-1f84-4326-a862-5289598f6e2d';

    /**
     * Customer UUID
     */
    public const CUSTOMER_ID = 'c2f1d888-232e-49eb-87ac-5f75363af13b';

    /**
     * Resource Block UUID (used in ResourceBlockUpdated webhooks)
     */
    public const BLOCK_ID = '7cccbdc6-73cf-4cd4-8056-6fd00f4d9699';

    /**
     * Message UUID (used in MessageAdded webhooks)
     */
    public const MESSAGE_ID = 'a1234567-89ab-cdef-0123-456789abcdef';

    /**
     * Rate UUID
     */
    public const RATE_ID = 'ed4b660b-19d0-434b-9360-a4de2101ed08';

    /**
     * Age Category UUID (Adult)
     */
    public const AGE_CATEGORY_ADULT_ID = '1f67644f-052d-4863-acdf-ae1600c60ca0';

    // ========================================================================
    // HELPER METHODS
    // ========================================================================

    /**
     * Get all available webhook fixture names
     *
     * @return array List of webhook fixture names (without .json)
     */
    public static function availableWebhooks(): array
    {
        $dir = __DIR__ . '/../../mocks/mews/webhooks/';
        $files = glob($dir . '*.json');

        return array_map(function ($file) {
            return basename($file, '.json');
        }, $files);
    }

    /**
     * Get all available API response fixture names
     *
     * @return array List of response fixture names (without .json)
     */
    public static function availableResponses(): array
    {
        $dir = __DIR__ . '/../../mocks/mews/responses/';
        $files = glob($dir . '*.json');

        return array_map(function ($file) {
            return basename($file, '.json');
        }, $files);
    }
}

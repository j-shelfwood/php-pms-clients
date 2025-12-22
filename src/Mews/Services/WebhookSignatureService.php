<?php

namespace Shelfwood\PhpPms\Mews\Services;

/**
 * Mews Webhook Signature Service
 *
 * Provides HMAC-SHA256 signature generation and verification for Mews webhooks.
 * Webhooks include X-Mews-Signature header computed as: HMAC-SHA256(json_payload, webhook_secret)
 *
 * @see https://mews-systems.gitbook.io/connector-api/webhooks/wh-general#security
 */
class WebhookSignatureService
{
    /**
     * Generate HMAC-SHA256 signature for webhook payload
     *
     * @param array $payload Webhook payload to sign
     * @param string $secret Webhook secret from Mews configuration
     * @return string HMAC-SHA256 signature (lowercase hex)
     */
    public static function generate(array $payload, string $secret): string
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
    public static function verify(array $payload, string $signature, string $secret): bool
    {
        $expected = self::generate($payload, $secret);
        return hash_equals($expected, $signature);
    }

    /**
     * Verify webhook signature with null-safe handling
     *
     * Returns false if signature is null/empty instead of throwing exception.
     *
     * @param array $payload Webhook payload
     * @param string|null $signature Signature from X-Mews-Signature header
     * @param string $secret Webhook secret
     * @return bool True if signature is valid
     */
    public static function verifySafe(array $payload, ?string $signature, string $secret): bool
    {
        if (empty($signature)) {
            return false;
        }

        return self::verify($payload, $signature, $secret);
    }
}

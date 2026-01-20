#!/usr/bin/env php
<?php

/**
 * Test script to fetch real resource blocks from Mews demo API
 * Usage: php scripts/test-resource-blocks-api.php
 */

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

$clientToken = '9381AB282F844CD9A2F4AD200158E7BC-D27113FA792B0855F87D0F93E9E1D71';
$accessToken = 'B811B453B8144A73B80CAD6E00805D62-B7899D9C0F3C579C86621146C4C74A2';
$baseUrl = 'https://api.mews-demo.com';

$client = new Client();

echo "Fetching resource blocks from Mews demo API...\n";
echo "Endpoint: {$baseUrl}/api/connector/v1/resourceBlocks/getAll\n\n";

try {
    $response = $client->post("{$baseUrl}/api/connector/v1/resourceBlocks/getAll", [
        'json' => [
            'ClientToken' => $clientToken,
            'AccessToken' => $accessToken,
            'Client' => 'PhpPmsClients/1.0',
            'CollidingUtc' => [
                'StartUtc' => '2025-12-01T00:00:00Z',
                'EndUtc' => '2026-02-28T23:59:59Z',
            ],
            'Limitation' => [
                'Count' => 10,  // Get first 10 blocks
            ],
        ],
        'timeout' => 30,
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
    ]);

    $statusCode = $response->getStatusCode();
    $body = (string) $response->getBody();
    $data = json_decode($body, true);

    echo "Status Code: {$statusCode}\n";
    echo "Response:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

    if (isset($data['ResourceBlocks']) && count($data['ResourceBlocks']) > 0) {
        echo "✅ Found " . count($data['ResourceBlocks']) . " resource blocks\n\n";

        echo "First Resource Block Structure:\n";
        echo "Fields: " . implode(', ', array_keys($data['ResourceBlocks'][0])) . "\n\n";

        echo "Full structure:\n";
        print_r($data['ResourceBlocks'][0]);
    } else {
        echo "⚠️  No resource blocks found in response\n";
        echo "Full response structure:\n";
        print_r($data);
    }

} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

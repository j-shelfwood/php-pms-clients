<?php

use Shelfwood\PhpPms\Mews\Config\MewsConfig;
use Shelfwood\PhpPms\Mews\MewsConnectorAPI;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

it('returns webhook secret when configured', function () {
    $config = new MewsConfig(
        clientToken: 'test-client-token',
        accessToken: 'test-access-token',
        baseUrl: 'https://api.mews-demo.com',
        webhookSecret: 'my-webhook-secret-123'
    );

    $api = new MewsConnectorAPI($config);

    expect($api->getWebhookSecret())->toBe('my-webhook-secret-123');
});

it('returns null when webhook secret not configured', function () {
    $config = new MewsConfig(
        clientToken: 'test-client-token',
        accessToken: 'test-access-token',
        baseUrl: 'https://api.mews-demo.com'
    );

    $api = new MewsConnectorAPI($config);

    expect($api->getWebhookSecret())->toBeNull();
});

it('fetches enterprise configuration', function () {
    $mockResponse = [
        'Enterprise' => [
            'Id' => '851df8c8-90f2-4c4a-8e01-a4fc46b25178',
            'Name' => 'Test Enterprise',
            'TimeZoneIdentifier' => 'Europe/Budapest',
        ],
        'NowUtc' => '2025-12-19T12:00:00Z',
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($mockResponse)),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $config = new MewsConfig(
        clientToken: 'test-client-token',
        accessToken: 'test-access-token',
        baseUrl: 'https://api.mews-demo.com'
    );

    $api = new MewsConnectorAPI($config, $client);
    $result = $api->getConfiguration();

    expect($result['Enterprise']['Id'])->toBe('851df8c8-90f2-4c4a-8e01-a4fc46b25178')
        ->and($result['Enterprise']['TimeZoneIdentifier'])->toBe('Europe/Budapest')
        ->and($result['NowUtc'])->toBe('2025-12-19T12:00:00Z');
});

it('extracts enterprise timezone', function () {
    $mockResponse = [
        'Enterprise' => [
            'Id' => '851df8c8-90f2-4c4a-8e01-a4fc46b25178',
            'Name' => 'Test Enterprise',
            'TimeZoneIdentifier' => 'America/New_York',
        ],
        'NowUtc' => '2025-12-19T12:00:00Z',
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($mockResponse)),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $config = new MewsConfig(
        clientToken: 'test-client-token',
        accessToken: 'test-access-token',
        baseUrl: 'https://api.mews-demo.com'
    );

    $api = new MewsConnectorAPI($config, $client);
    $timezone = $api->getEnterpriseTimezone();

    expect($timezone)->toBe('America/New_York');
});

it('throws exception when timezone not found in configuration', function () {
    $mockResponse = [
        'Enterprise' => [
            'Id' => '851df8c8-90f2-4c4a-8e01-a4fc46b25178',
            'Name' => 'Test Enterprise',
        ],
        'NowUtc' => '2025-12-19T12:00:00Z',
    ];

    $mock = new MockHandler([
        new Response(200, [], json_encode($mockResponse)),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $config = new MewsConfig(
        clientToken: 'test-client-token',
        accessToken: 'test-access-token',
        baseUrl: 'https://api.mews-demo.com'
    );

    $api = new MewsConnectorAPI($config, $client);

    $api->getEnterpriseTimezone();
})->throws(\RuntimeException::class, 'Enterprise timezone not found');

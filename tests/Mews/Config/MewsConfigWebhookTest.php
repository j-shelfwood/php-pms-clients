<?php

use Shelfwood\PhpPms\Mews\Config\MewsConfig;

it('creates config with webhook secret', function () {
    $config = new MewsConfig(
        clientToken: 'test-client-token',
        accessToken: 'test-access-token',
        baseUrl: 'https://api.mews-demo.com',
        webhookSecret: 'test-webhook-secret'
    );

    expect($config->webhookSecret)->toBe('test-webhook-secret');
});

it('creates config without webhook secret', function () {
    $config = new MewsConfig(
        clientToken: 'test-client-token',
        accessToken: 'test-access-token',
        baseUrl: 'https://api.mews-demo.com'
    );

    expect($config->webhookSecret)->toBeNull();
});

it('creates from array with webhook secret', function () {
    $config = MewsConfig::fromArray([
        'client_token' => 'test-client-token',
        'access_token' => 'test-access-token',
        'base_url' => 'https://api.mews-demo.com',
        'webhook_secret' => 'test-webhook-secret',
    ]);

    expect($config->webhookSecret)->toBe('test-webhook-secret');
});

it('creates from array without webhook secret', function () {
    $config = MewsConfig::fromArray([
        'client_token' => 'test-client-token',
        'access_token' => 'test-access-token',
        'base_url' => 'https://api.mews-demo.com',
    ]);

    expect($config->webhookSecret)->toBeNull();
});

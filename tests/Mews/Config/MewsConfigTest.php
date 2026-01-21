<?php

use Shelfwood\PhpPms\Mews\Config\MewsConfig;

it('creates valid config', function () {
    $config = new MewsConfig(
        clientToken: 'test_token',
        accessToken: 'test_access',
        baseUrl: 'https://api.mews-demo.com',
        clientName: 'Test/1.0'
    );

    expect($config->clientToken)->toBe('test_token')
        ->and($config->accessToken)->toBe('test_access')
        ->and($config->baseUrl)->toBe('https://api.mews-demo.com')
        ->and($config->rateLimitEnabled)->toBeTrue()
        ->and($config->rateLimitMaxRequests)->toBe(180)
        ->and($config->rateLimitWindowSeconds)->toBe(30);
});

it('validates required fields', function () {
    new MewsConfig(
        clientToken: '',
        accessToken: 'test'
    );
})->throws(\InvalidArgumentException::class, 'Client token is required');

it('validates URL format', function () {
    new MewsConfig(
        clientToken: 'test',
        accessToken: 'test',
        baseUrl: 'not-a-url'
    );
})->throws(\InvalidArgumentException::class, 'Invalid base URL format');

it('validates rate limit max requests', function () {
    new MewsConfig(
        clientToken: 'test',
        accessToken: 'test',
        rateLimitMaxRequests: 0
    );
})->throws(\InvalidArgumentException::class, 'Rate limit max requests must be positive');

it('validates rate limit window seconds', function () {
    new MewsConfig(
        clientToken: 'test',
        accessToken: 'test',
        rateLimitWindowSeconds: 0
    );
})->throws(\InvalidArgumentException::class, 'Rate limit window seconds must be positive');

it('creates from array', function () {
    $config = MewsConfig::fromArray([
        'client_token' => 'test_token',
        'access_token' => 'test_access',
        'base_url' => 'https://api.mews-demo.com',
        'rate_limit_enabled' => false,
        'rate_limit_max_requests' => 120,
        'rate_limit_window_seconds' => 60,
    ]);

    expect($config->clientToken)->toBe('test_token');
    expect($config->rateLimitEnabled)->toBeFalse()
        ->and($config->rateLimitMaxRequests)->toBe(120)
        ->and($config->rateLimitWindowSeconds)->toBe(60);
});

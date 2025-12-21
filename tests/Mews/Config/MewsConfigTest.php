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
        ->and($config->baseUrl)->toBe('https://api.mews-demo.com');
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

it('creates from array', function () {
    $config = MewsConfig::fromArray([
        'client_token' => 'test_token',
        'access_token' => 'test_access',
        'base_url' => 'https://api.mews-demo.com',
    ]);

    expect($config->clientToken)->toBe('test_token');
});

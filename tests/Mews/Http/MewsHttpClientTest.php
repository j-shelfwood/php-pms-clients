<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Shelfwood\PhpPms\Mews\Config\MewsConfig;
use Tests\Support\InMemoryCache;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Exceptions\NetworkException;

beforeEach(function () {
    $this->config = new MewsConfig(
        clientToken: 'test_client_token',
        accessToken: 'test_access_token',
        baseUrl: 'https://api.mews-demo.com',
        clientName: 'TestClient/1.0',
        rateLimitEnabled: false
    );
});

it('builds request body with authentication', function () {
    $httpClient = Mockery::mock(Client::class);
    $mewsClient = new MewsHttpClient($this->config, $httpClient);

    $body = $mewsClient->buildRequestBody(['ServiceId' => 'test-123']);

    expect($body)->toHaveKeys(['ClientToken', 'AccessToken', 'Client', 'ServiceId'])
        ->and($body['ClientToken'])->toBe('test_client_token')
        ->and($body['AccessToken'])->toBe('test_access_token')
        ->and($body['ServiceId'])->toBe('test-123');
});

it('makes POST request successfully', function () {
    $mockResponse = new Response(200, [], json_encode(['Services' => []]));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $result = $mewsClient->post('/api/connector/v1/services/getAll', []);

    expect($result)->toHaveKey('Services');
});

it('throttles when rate limit exceeded', function () {
    $config = new MewsConfig(
        clientToken: 'test_client_token',
        accessToken: 'test_access_token',
        baseUrl: 'https://api.mews-demo.com',
        clientName: 'TestClient/1.0',
        rateLimitEnabled: true,
        rateLimitMaxRequests: 1,
        rateLimitWindowSeconds: 1
    );

    $mockResponse = new Response(200, [], json_encode(['Services' => []]));
    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->times(2)
        ->andReturn($mockResponse);

    $cache = new InMemoryCache();
    $mewsClient = new MewsHttpClient($config, $httpClient, null, $cache);

    $start = microtime(true);
    $mewsClient->post('/api/connector/v1/services/getAll', []);
    $mewsClient->post('/api/connector/v1/services/getAll', []);
    $elapsed = microtime(true) - $start;

    expect($elapsed)->toBeGreaterThan(0.9);
});

it('skips throttling when cache is missing', function () {
    $config = new MewsConfig(
        clientToken: 'test_client_token',
        accessToken: 'test_access_token',
        baseUrl: 'https://api.mews-demo.com',
        clientName: 'TestClient/1.0',
        rateLimitEnabled: true,
        rateLimitMaxRequests: 1,
        rateLimitWindowSeconds: 1
    );

    $mockResponse = new Response(200, [], json_encode(['Services' => []]));
    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->times(2)
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($config, $httpClient);

    $start = microtime(true);
    $mewsClient->post('/api/connector/v1/services/getAll', []);
    $mewsClient->post('/api/connector/v1/services/getAll', []);
    $elapsed = microtime(true) - $start;

    expect($elapsed)->toBeLessThan(0.2);
});

it('throws exception on API error', function () {
    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->andThrow(new \GuzzleHttp\Exception\RequestException(
            'API Error',
            new \GuzzleHttp\Psr7\Request('POST', '/test')
        ));

    $mewsClient = new MewsHttpClient($this->config, $httpClient);

    $mewsClient->post('/api/connector/v1/test', []);
})->throws(NetworkException::class);

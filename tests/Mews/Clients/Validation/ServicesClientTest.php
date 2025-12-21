<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Shelfwood\PhpPms\Mews\Config\MewsConfig;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Clients\Validation\ServicesClient;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Service;
use Shelfwood\PhpPms\Mews\Exceptions\MewsApiException;

beforeEach(function () {
    $this->config = new MewsConfig(
        clientToken: 'test_client_token',
        accessToken: 'test_access_token',
        baseUrl: 'https://api.mews-demo.com',
        clientName: 'TestClient/1.0'
    );

    // Load mock response data
    $this->mockData = json_decode(
        file_get_contents(__DIR__ . '/../../../../mocks/mews/responses/services-getall.json'),
        true
    );
});

it('gets all services', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/services/getAll#'),
            Mockery::on(function ($options) {
                $body = json_decode($options['body'], true);
                expect($body)->toHaveKeys(['ClientToken', 'AccessToken']);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $servicesClient = new ServicesClient($mewsClient);

    $response = $servicesClient->getAll();

    expect($response->items)->not->toBeEmpty()
        ->and($response->items[0])->toBeInstanceOf(Service::class);
});

it('gets services by IDs', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                $body = json_decode($options['body'], true);
                expect($body['ServiceIds'])->toBe(['service-1', 'service-2']);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $servicesClient = new ServicesClient($mewsClient);

    $servicesClient->getAll(['service-1', 'service-2']);
});

it('gets service by ID successfully', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                $body = json_decode($options['body'], true);
                expect($body['ServiceIds'])->toBe(['test-service-id']);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $servicesClient = new ServicesClient($mewsClient);

    $service = $servicesClient->getById('test-service-id');

    expect($service)->toBeInstanceOf(Service::class);
});

it('throws exception when service not found by ID', function () {
    $mockResponse = new Response(200, [], json_encode(['Services' => []]));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $servicesClient = new ServicesClient($mewsClient);

    $servicesClient->getById('non-existent-service');
})->throws(MewsApiException::class, 'Service not found');

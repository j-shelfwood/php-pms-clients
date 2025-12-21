<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Shelfwood\PhpPms\Mews\Config\MewsConfig;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Clients\Validation\ResourcesClient;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Resource;
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
        file_get_contents(__DIR__ . '/../../../../mocks/mews/responses/resources-getall.json'),
        true
    );
});

it('gets all resources with service filter', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/resources/getAll#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ClientToken', 'AccessToken', 'ServiceIds'])
                    ->and($body['ServiceIds'])->toBeArray();
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $resourcesClient = new ResourcesClient($mewsClient);

    $response = $resourcesClient->getAll(serviceIds: ['test-service-id']);

    expect($response->items)->toHaveCount(10)
        ->and($response->items[0])->toBeInstanceOf(Resource::class)
        ->and($response->items[0]->name)->toBeString();
});

it('gets all resources with category filter', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKey('ResourceCategoryIds')
                    ->and($body['ResourceCategoryIds'])->toBe(['category-123']);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $resourcesClient = new ResourcesClient($mewsClient);

    $resourcesClient->getAll(resourceCategoryIds: ['category-123']);
});

it('gets all resources with resource IDs filter', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKey('ResourceIds')
                    ->and($body['ResourceIds'])->toBe(['resource-1', 'resource-2']);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $resourcesClient = new ResourcesClient($mewsClient);

    $resourcesClient->getAll(resourceIds: ['resource-1', 'resource-2']);
});

it('gets resources for service using helper method', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body['ServiceIds'])->toBe(['service-123']);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $resourcesClient = new ResourcesClient($mewsClient);

    $response = $resourcesClient->getForService('service-123');

    expect($response->items)->toHaveCount(10);
});

it('gets resources for category using helper method', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body['ResourceCategoryIds'])->toBe(['category-456']);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $resourcesClient = new ResourcesClient($mewsClient);

    $response = $resourcesClient->getForCategory('category-456');

    expect($response->items)->toHaveCount(10);
});

it('gets resource by ID successfully', function () {
    $singleResourceData = [
        'Resources' => [$this->mockData['Resources'][0]],
        'Cursor' => null
    ];

    $mockResponse = new Response(200, [], json_encode($singleResourceData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body['ResourceIds'])->toBe(['095a6d7f-4893-4a3b-9c35-ff595d4bfa0c']);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $resourcesClient = new ResourcesClient($mewsClient);

    $resource = $resourcesClient->getById('095a6d7f-4893-4a3b-9c35-ff595d4bfa0c');

    expect($resource)->toBeInstanceOf(Resource::class)
        ->and($resource->id)->toBe('095a6d7f-4893-4a3b-9c35-ff595d4bfa0c')
        ->and($resource->name)->toBeString()
        ->and($resource->state)->toBe('Clean');
});

it('throws exception when resource not found by ID', function () {
    $mockResponse = new Response(200, [], json_encode(['Resources' => [], 'Cursor' => null]));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $resourcesClient = new ResourcesClient($mewsClient);

    $resourcesClient->getById('non-existent-id');
})->throws(MewsApiException::class, 'Resource not found');

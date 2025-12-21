<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Shelfwood\PhpPms\Mews\Config\MewsConfig;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Clients\Validation\ResourceCategoriesClient;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceCategory;

beforeEach(function () {
    $this->config = new MewsConfig(
        clientToken: 'test_client_token',
        accessToken: 'test_access_token',
        baseUrl: 'https://api.mews-demo.com',
        clientName: 'TestClient/1.0'
    );

    // Load mock response data
    $this->mockData = json_decode(
        file_get_contents(__DIR__ . '/../../../../mocks/mews/responses/resourcecategories-getall.json'),
        true
    );
});

it('gets resource categories for service', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/resourceCategories/getAll#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ClientToken', 'AccessToken', 'ServiceIds'])
                    ->and($body['ServiceIds'])->toBeArray()
                    ->and($body['ServiceIds'])->toContain('test-service-id');
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $resourceCategoriesClient = new ResourceCategoriesClient($mewsClient);

    $response = $resourceCategoriesClient->getForService('test-service-id');

    expect($response->items)->not->toBeEmpty()
        ->and($response->items[0])->toBeInstanceOf(ResourceCategory::class);
});

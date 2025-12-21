<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Shelfwood\PhpPms\Mews\Config\MewsConfig;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Clients\Validation\AgeCategoriesClient;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\AgeCategory;

beforeEach(function () {
    $this->config = new MewsConfig(
        clientToken: 'test_client_token',
        accessToken: 'test_access_token',
        baseUrl: 'https://api.mews-demo.com',
        clientName: 'TestClient/1.0'
    );

    // Load mock response data
    $this->mockData = json_decode(
        file_get_contents(__DIR__ . '/../../../../mocks/mews/responses/agecategories-getall.json'),
        true
    );
});

it('gets all age categories for service', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/ageCategories/getAll#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ClientToken', 'AccessToken', 'ServiceIds', 'Limitation'])
                    ->and($body['ServiceIds'])->toBeArray()
                    ->and($body['Limitation']['Count'])->toBe(100);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $ageCategoriesClient = new AgeCategoriesClient($mewsClient);

    $response = $ageCategoriesClient->getAll('ec9d261c-1ef1-4a6e-8565-ad7200d77411');

    expect($response->items)->toHaveCount(2)
        ->and($response->items[0])->toBeInstanceOf(AgeCategory::class)
        ->and($response->items[0]->classification)->toBe('Adult')
        ->and($response->items[1]->classification)->toBe('Child');
});

it('gets adult category successfully', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $ageCategoriesClient = new AgeCategoriesClient($mewsClient);

    $adultCategory = $ageCategoriesClient->getAdultCategory('ec9d261c-1ef1-4a6e-8565-ad7200d77411');

    expect($adultCategory)->toBeInstanceOf(AgeCategory::class)
        ->and($adultCategory->classification)->toBe('Adult')
        ->and($adultCategory->isActive)->toBeTrue()
        ->and($adultCategory->id)->toBe('d39dcfc0-69c5-43fe-b28e-ade3011a680a');
});

it('gets child category successfully', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $ageCategoriesClient = new AgeCategoriesClient($mewsClient);

    $childCategory = $ageCategoriesClient->getChildCategory('ec9d261c-1ef1-4a6e-8565-ad7200d77411');

    expect($childCategory)->toBeInstanceOf(AgeCategory::class)
        ->and($childCategory->classification)->toBe('Child')
        ->and($childCategory->isActive)->toBeTrue()
        ->and($childCategory->id)->toBe('3d76a1c6-a7c7-40df-badc-ade3011a680a')
        ->and($childCategory->minimalAge)->toBe(0)
        ->and($childCategory->maximalAge)->toBe(18);
});

it('returns null when adult category not found', function () {
    $mockData = [
        'AgeCategories' => [
            [
                'Id' => '3d76a1c6-a7c7-40df-badc-ade3011a680a',
                'ServiceId' => 'test-service',
                'Classification' => 'Child',
                'IsActive' => true,
                'Names' => ['en-GB' => 'Children'],
                'MinimalAge' => 0,
                'MaximalAge' => 18,
            ]
        ],
        'Cursor' => null
    ];

    $mockResponse = new Response(200, [], json_encode($mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $ageCategoriesClient = new AgeCategoriesClient($mewsClient);

    $adultCategory = $ageCategoriesClient->getAdultCategory('test-service');

    expect($adultCategory)->toBeNull();
});

it('returns null when child category not found', function () {
    $mockData = [
        'AgeCategories' => [
            [
                'Id' => 'd39dcfc0-69c5-43fe-b28e-ade3011a680a',
                'ServiceId' => 'test-service',
                'Classification' => 'Adult',
                'IsActive' => true,
                'Names' => ['en-GB' => 'Adults'],
            ]
        ],
        'Cursor' => null
    ];

    $mockResponse = new Response(200, [], json_encode($mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $ageCategoriesClient = new AgeCategoriesClient($mewsClient);

    $childCategory = $ageCategoriesClient->getChildCategory('test-service');

    expect($childCategory)->toBeNull();
});

it('skips inactive age categories', function () {
    $mockData = [
        'AgeCategories' => [
            [
                'Id' => 'inactive-adult',
                'ServiceId' => 'test-service',
                'Classification' => 'Adult',
                'IsActive' => false,
                'Names' => ['en-GB' => 'Inactive Adults'],
            ],
            [
                'Id' => 'active-adult',
                'ServiceId' => 'test-service',
                'Classification' => 'Adult',
                'IsActive' => true,
                'Names' => ['en-GB' => 'Active Adults'],
            ]
        ],
        'Cursor' => null
    ];

    $mockResponse = new Response(200, [], json_encode($mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $ageCategoriesClient = new AgeCategoriesClient($mewsClient);

    $adultCategory = $ageCategoriesClient->getAdultCategory('test-service');

    // Should return active category, not inactive one
    expect($adultCategory->id)->toBe('active-adult')
        ->and($adultCategory->isActive)->toBeTrue();
});

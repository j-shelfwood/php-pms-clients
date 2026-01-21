<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Shelfwood\PhpPms\Mews\Config\MewsConfig;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Clients\Validation\ResourceCategoryAssignmentsClient;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceCategoryAssignment;

beforeEach(function () {
    $this->config = new MewsConfig(
        clientToken: 'test_client_token',
        accessToken: 'test_access_token',
        baseUrl: 'https://api.mews-demo.com',
        clientName: 'TestClient/1.0'
    );

    // Load mock response data
    $this->mockData = json_decode(
        file_get_contents(__DIR__ . '/../../../../mocks/mews/responses/resourcecategoryassignments-getall.json'),
        true
    );
});

it('gets all resource category assignments', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/resourceCategoryAssignments/getAll#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ClientToken', 'AccessToken', 'Limitation'])
                    ->and($body['Limitation']['Count'])->toBe(1000);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $assignmentsClient = new ResourceCategoryAssignmentsClient($mewsClient);

    $response = $assignmentsClient->getAll();

    expect($response->items)->toBeEmpty();
});

it('filters assignments by resource category IDs', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKey('ResourceCategoryIds')
                    ->and($body['ResourceCategoryIds'])->toBe(['category-1', 'category-2']);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $assignmentsClient = new ResourceCategoryAssignmentsClient($mewsClient);

    $assignmentsClient->getAll(resourceCategoryIds: ['category-1', 'category-2']);
});

it('throws when filtering by resource IDs without category IDs', function () {
    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldNotReceive('post');

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $assignmentsClient = new ResourceCategoryAssignmentsClient($mewsClient);

    $assignmentsClient->getAll(resourceIds: ['resource-1']);
})->throws(\InvalidArgumentException::class, 'ResourceCategoryIds is required when filtering by ResourceIds');

it('filters assignments by activity states', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKey('ActivityStates')
                    ->and($body['ActivityStates'])->toBe(['Active']);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $assignmentsClient = new ResourceCategoryAssignmentsClient($mewsClient);

    $assignmentsClient->getAll(activityStates: ['Active']);
});

it('enforces limit count boundaries', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->times(3)
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $assignmentsClient = new ResourceCategoryAssignmentsClient($mewsClient);

    // Test max limit (1000)
    $assignmentsClient->getAll(limitCount: 5000);

    // Test min limit (1)
    $assignmentsClient->getAll(limitCount: 0);

    // Test normal limit
    $assignmentsClient->getAll(limitCount: 500);
});

<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Config\MewsConfig;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Clients\Production\AvailabilityClient;
use Shelfwood\PhpPms\Mews\Payloads\GetAvailabilityPayload;
use Shelfwood\PhpPms\Mews\Responses\AvailabilityResponse;

beforeEach(function () {
    $this->config = new MewsConfig(
        clientToken: 'test_client_token',
        accessToken: 'test_access_token',
        baseUrl: 'https://api.mews-demo.com',
        clientName: 'TestClient/1.0'
    );

    // Load mock response data
    $this->mockData = json_decode(
        file_get_contents(__DIR__ . '/../../../../mocks/mews/responses/services-getavailability.json'),
        true
    );
});

it('gets availability successfully', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/services/getAvailability#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ClientToken', 'AccessToken', 'ServiceId', 'ResourceCategoryIds', 'FirstTimeUnitStartUtc', 'LastTimeUnitStartUtc']);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $availabilityClient = new AvailabilityClient($mewsClient);

    $payload = new GetAvailabilityPayload(
        serviceId: 'bd26d8db-86a4-4f18-9e94-1b2362a1073c',
        firstTimeUnitStartUtc: Carbon::parse('2025-12-19'),
        lastTimeUnitStartUtc: Carbon::parse('2025-12-23'),
        resourceCategoryIds: ['44bd8ad0-e70b-4bd9-8445-ad7200d7c349']
    );

    $response = $availabilityClient->get($payload);

    expect($response)->toBeInstanceOf(AvailabilityResponse::class)
        ->and($response->categoryAvailabilities)->toHaveCount(1)
        ->and($response->categoryAvailabilities[0]->categoryId)->toBe('44bd8ad0-e70b-4bd9-8445-ad7200d7c349')
        ->and($response->categoryAvailabilities[0]->availabilities)->toBe([5, 4, 3, 2, 1])
        ->and($response->categoryAvailabilities[0]->adjustments)->toBe([0, -1, -1, -1, -1])
        ->and($response->timeUnitStartsUtc)->toHaveCount(5);
});

it('handles empty availability response', function () {
    $mockResponse = new Response(200, [], json_encode([
        'CategoryAvailabilities' => [],
        'TimeUnitStartsUtc' => []
    ]));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $availabilityClient = new AvailabilityClient($mewsClient);

    $payload = new GetAvailabilityPayload(
        serviceId: 'test-service',
        firstTimeUnitStartUtc: Carbon::parse('2025-01-01'),
        lastTimeUnitStartUtc: Carbon::parse('2025-01-05')
    );

    $response = $availabilityClient->get($payload);

    expect($response->categoryAvailabilities)->toBeEmpty()
        ->and($response->timeUnitStartsUtc)->toBeEmpty();
});

it('sends correct request structure', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/services/getAvailability#'),
            Mockery::on(function ($options) {
                $body = $options['json'];

                // Verify request structure
                expect($body['ServiceId'])->toBeString()
                    ->and($body['FirstTimeUnitStartUtc'])->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/')
                    ->and($body['LastTimeUnitStartUtc'])->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/');

                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $availabilityClient = new AvailabilityClient($mewsClient);

    $payload = new GetAvailabilityPayload(
        serviceId: 'test-service-id',
        firstTimeUnitStartUtc: Carbon::parse('2025-01-01'),
        lastTimeUnitStartUtc: Carbon::parse('2025-01-31')
    );

    $availabilityClient->get($payload);
});

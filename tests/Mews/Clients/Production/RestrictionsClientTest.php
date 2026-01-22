<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Config\MewsConfig;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Clients\Production\RestrictionsClient;
use Shelfwood\PhpPms\Mews\Responses\RestrictionsResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Restriction;

beforeEach(function () {
    $this->config = new MewsConfig(
        clientToken: 'test_client_token',
        accessToken: 'test_access_token',
        baseUrl: 'https://api.mews-demo.com',
        clientName: 'TestClient/1.0'
    );

    // Load mock response data
    $this->mockData = json_decode(
        file_get_contents(__DIR__ . '/../../../../mocks/mews/responses/restrictions-getall.json'),
        true
    );
});

it('gets all restrictions for service and date range', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/restrictions/getAll#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ClientToken', 'AccessToken', 'ServiceIds', 'CollidingUtc', 'Limitation'])
                    ->and($body['ServiceIds'])->toBeArray()
                    ->and($body['ServiceIds'])->toHaveCount(1)
                    ->and($body['CollidingUtc'])->toHaveKeys(['StartUtc', 'EndUtc'])
                    ->and($body['Limitation'])->toHaveKey('Count');
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $restrictionsClient = new RestrictionsClient($mewsClient);

    $response = $restrictionsClient->getAll(
        serviceId: 'bd26d8db-86a4-4f18-9e94-1b2362a1073c',
        start: Carbon::parse('2025-07-01'),
        end: Carbon::parse('2025-12-31')
    );

    expect($response)->toBeInstanceOf(RestrictionsResponse::class)
        ->and($response->items)->toHaveCount(1)
        ->and($response->items[0])->toBeInstanceOf(Restriction::class)
        ->and($response->items[0]->exceptions->minLength)->toBe('P0M3DT0H0M0S');
});

it('handles paginated responses with cursor', function () {
    // First page with cursor
    $firstPage = [
        'Restrictions' => [$this->mockData['Restrictions'][0]],
        'Cursor' => 'next-page-token'
    ];

    // Second page without cursor (end of data)
    $secondPage = [
        'Restrictions' => [],
        'Cursor' => null
    ];

    $mockFirstResponse = new Response(200, [], json_encode($firstPage));
    $mockSecondResponse = new Response(200, [], json_encode($secondPage));

    $httpClient = Mockery::mock(Client::class);

    // First request (no cursor)
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->not->toHaveKey('Cursor');
                return true;
            })
        )
        ->andReturn($mockFirstResponse);

    // Second request (with cursor)
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body['Cursor'])->toBe('next-page-token');
                return true;
            })
        )
        ->andReturn($mockSecondResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $restrictionsClient = new RestrictionsClient($mewsClient);

    $response = $restrictionsClient->getAll(
        serviceId: 'test-service',
        start: Carbon::parse('2025-01-01'),
        end: Carbon::parse('2025-12-31')
    );

    // Should aggregate both pages
    expect($response->items)->toHaveCount(1);
});

it('filters by resource category IDs', function () {
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
    $restrictionsClient = new RestrictionsClient($mewsClient);

    $restrictionsClient->getAll(
        serviceId: 'test-service',
        start: Carbon::parse('2025-01-01'),
        end: Carbon::parse('2025-12-31'),
        resourceCategoryIds: ['category-1', 'category-2']
    );
});

it('finds minimum stay for date and category', function () {
    $restrictions = [
        Restriction::map([
            'Id' => 'restriction-1',
            'ServiceId' => 'service-1',
            'Conditions' => [
                'Type' => 'Stay',
                'ResourceCategoryId' => 'category-1',
                'StartUtc' => '2025-12-20T00:00:00Z',
                'EndUtc' => '2026-01-05T00:00:00Z',
                'Days' => [],
                'Hours' => [],
            ],
            'Exceptions' => [
                'MinLength' => 'P0M3DT0H0M0S',
            ],
        ]),
        Restriction::map([
            'Id' => 'restriction-2',
            'ServiceId' => 'service-1',
            'Conditions' => [
                'Type' => 'Stay',
                'ResourceCategoryId' => 'category-1',
                'StartUtc' => '2025-12-25T00:00:00Z',
                'EndUtc' => '2026-01-02T00:00:00Z',
                'Days' => [],
                'Hours' => [],
            ],
            'Exceptions' => [
                'MinLength' => 'P0M5DT0H0M0S',
            ],
        ]),
    ];

    $httpClient = Mockery::mock(Client::class);
    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $restrictionsClient = new RestrictionsClient($mewsClient);

    // Date within both restrictions (should return first one found)
    $minimumStay = $restrictionsClient->findMinimumStayForDate(
        restrictions: $restrictions,
        date: Carbon::parse('2025-12-28'),
        resourceCategoryId: 'category-1'
    );

    expect($minimumStay)->toBe('P0M3DT0H0M0S');
});

it('finds minimum stay when only one restriction applies', function () {
    $restrictions = [
        Restriction::map([
            'Id' => 'restriction-1',
            'ServiceId' => 'service-1',
            'Conditions' => [
                'Type' => 'Stay',
                'ResourceCategoryId' => 'category-1',
                'StartUtc' => '2025-12-20T00:00:00Z',
                'EndUtc' => '2026-01-05T00:00:00Z',
                'Days' => [],
                'Hours' => [],
            ],
            'Exceptions' => [
                'MinLength' => 'P0M3DT0H0M0S',
            ],
        ]),
    ];

    $httpClient = Mockery::mock(Client::class);
    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $restrictionsClient = new RestrictionsClient($mewsClient);

    $minimumStay = $restrictionsClient->findMinimumStayForDate(
        restrictions: $restrictions,
        date: Carbon::parse('2025-12-25'),
        resourceCategoryId: 'category-1'
    );

    expect($minimumStay)->toBe('P0M3DT0H0M0S');
});

it('returns null when no restrictions apply for date', function () {
    $restrictions = [
        Restriction::map([
            'Id' => 'restriction-1',
            'ServiceId' => 'service-1',
            'Conditions' => [
                'Type' => 'Stay',
                'ResourceCategoryId' => 'category-1',
                'StartUtc' => '2025-12-20T00:00:00Z',
                'EndUtc' => '2026-01-05T00:00:00Z',
                'Days' => [],
                'Hours' => [],
            ],
            'Exceptions' => [
                'MinLength' => 'P0M3DT0H0M0S',
            ],
        ]),
    ];

    $httpClient = Mockery::mock(Client::class);
    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $restrictionsClient = new RestrictionsClient($mewsClient);

    // Date outside restriction range
    $minimumStay = $restrictionsClient->findMinimumStayForDate(
        restrictions: $restrictions,
        date: Carbon::parse('2025-11-01'),
        resourceCategoryId: 'category-1'
    );

    expect($minimumStay)->toBeNull();
});

it('returns null when no restrictions apply for category', function () {
    $restrictions = [
        Restriction::map([
            'Id' => 'restriction-1',
            'ServiceId' => 'service-1',
            'Conditions' => [
                'Type' => 'Stay',
                'ResourceCategoryId' => 'category-1',
                'StartUtc' => '2025-12-20T00:00:00Z',
                'EndUtc' => '2026-01-05T00:00:00Z',
                'Days' => [],
                'Hours' => [],
            ],
            'Exceptions' => [
                'MinLength' => 'P0M3DT0H0M0S',
            ],
        ]),
    ];

    $httpClient = Mockery::mock(Client::class);
    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $restrictionsClient = new RestrictionsClient($mewsClient);

    // Different category
    $minimumStay = $restrictionsClient->findMinimumStayForDate(
        restrictions: $restrictions,
        date: Carbon::parse('2025-12-25'),
        resourceCategoryId: 'category-2'
    );

    expect($minimumStay)->toBeNull();
});

it('handles restrictions without minimum stay set', function () {
    $restrictions = [
        Restriction::map([
            'Id' => 'restriction-1',
            'ServiceId' => 'service-1',
            'Conditions' => [
                'Type' => 'Start',
                'ResourceCategoryId' => 'category-1',
                'StartUtc' => '2025-12-20T00:00:00Z',
                'EndUtc' => '2026-01-05T00:00:00Z',
                'Days' => [],
                'Hours' => [],
            ],
            'Exceptions' => [
                'MinLength' => null,
            ],
        ]),
    ];

    $httpClient = Mockery::mock(Client::class);
    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $restrictionsClient = new RestrictionsClient($mewsClient);

    $minimumStay = $restrictionsClient->findMinimumStayForDate(
        restrictions: $restrictions,
        date: Carbon::parse('2025-12-25'),
        resourceCategoryId: 'category-1'
    );

    expect($minimumStay)->toBeNull();
});

it('detects infinite loop when API returns same cursor repeatedly', function () {
    // Mock API returning same cursor on every request (infinite loop bug)
    $infinitePage = [
        'Restrictions' => [$this->mockData['Restrictions'][0]],
        'Cursor' => 'stuck-cursor'
    ];

    $mockResponse = new Response(200, [], json_encode($infinitePage));

    $httpClient = Mockery::mock(Client::class);
    
    // First request (no cursor)
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->not->toHaveKey('Cursor');
                return true;
            })
        )
        ->andReturn($mockResponse);

    // Second request (with cursor) - returns SAME cursor again
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body['Cursor'])->toBe('stuck-cursor');
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $restrictionsClient = new RestrictionsClient($mewsClient);

    // Should detect infinite loop and break (not hang)
    $response = $restrictionsClient->getAll(
        serviceId: 'test-service',
        start: Carbon::parse('2025-01-01'),
        end: Carbon::parse('2025-12-31')
    );

    // Should have deduplicated results (only 1 item, not infinite)
    expect($response->items)->toHaveCount(1);
});

it('throws exception when pagination exceeds maximum pages', function () {
    // Mock API that returns different cursors infinitely (never null)
    $httpClient = Mockery::mock(Client::class);
    
    // Mock unlimited pages - each returns a unique cursor
    $httpClient->shouldReceive('post')
        ->times(100) // Will be called 100 times before throwing
        ->andReturnUsing(function () {
            static $counter = 0;
            $counter++;
            $page = [
                'Restrictions' => [
                    [
                        'Id' => 'restriction-' . $counter,
                        'ServiceId' => 'test-service',
                        'Conditions' => ['Type' => 'Stay', 'Days' => [], 'Hours' => []],
                        'Exceptions' => []
                    ]
                ],
                'Cursor' => 'cursor-' . ($counter + 1) // Always return a new cursor
            ];
            return new Response(200, [], json_encode($page));
        });

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $restrictionsClient = new RestrictionsClient($mewsClient);

    // Should throw exception on 101st attempt (after 100 pages)
    $restrictionsClient->getAll(
        serviceId: 'test-service',
        start: Carbon::parse('2025-01-01'),
        end: Carbon::parse('2025-12-31')
    );
})->throws(
    \Shelfwood\PhpPms\Mews\Exceptions\MewsApiException::class,
    'Restrictions pagination exceeded 100 pages'
);

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
        end: Carbon::parse('2025-09-01') // 62 days — single chunk
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
        end: Carbon::parse('2025-03-01') // 59 days — single chunk
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
        end: Carbon::parse('2025-03-01'), // 59 days — single chunk
        resourceCategoryIds: ['category-1', 'category-2']
    );
});

it('returns most restrictive minimum stay when multiple Stay restrictions overlap', function () {
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
            'Exceptions' => ['MinLength' => 'P0M3DT0H0M0S'],
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
            'Exceptions' => ['MinLength' => 'P0M5DT0H0M0S'],
        ]),
    ];

    $restrictionsClient = new RestrictionsClient(new MewsHttpClient($this->config, Mockery::mock(Client::class)));

    // Date within both — 5 nights wins over 3 nights
    expect($restrictionsClient->findMinimumStayForDate($restrictions, Carbon::parse('2025-12-28'), 'category-1'))->toBe(5);
});

it('returns minimum stay as int for single matching Stay restriction', function () {
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
            'Exceptions' => ['MinLength' => 'P0M3DT0H0M0S'],
        ]),
    ];

    $restrictionsClient = new RestrictionsClient(new MewsHttpClient($this->config, Mockery::mock(Client::class)));

    expect($restrictionsClient->findMinimumStayForDate($restrictions, Carbon::parse('2025-12-25'), 'category-1'))->toBe(3);
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
            'Exceptions' => ['MinLength' => 'P0M3DT0H0M0S'],
        ]),
    ];

    $restrictionsClient = new RestrictionsClient(new MewsHttpClient($this->config, Mockery::mock(Client::class)));

    expect($restrictionsClient->findMinimumStayForDate($restrictions, Carbon::parse('2025-11-01'), 'category-1'))->toBeNull();
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
            'Exceptions' => ['MinLength' => 'P0M3DT0H0M0S'],
        ]),
    ];

    $restrictionsClient = new RestrictionsClient(new MewsHttpClient($this->config, Mockery::mock(Client::class)));

    expect($restrictionsClient->findMinimumStayForDate($restrictions, Carbon::parse('2025-12-25'), 'category-2'))->toBeNull();
});

it('ignores Start and End type restrictions for minimum stay', function () {
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
            'Exceptions' => ['MinLength' => 'P0M5DT0H0M0S'],
        ]),
    ];

    $restrictionsClient = new RestrictionsClient(new MewsHttpClient($this->config, Mockery::mock(Client::class)));

    // Start-type restriction must not affect stay_minimum
    expect($restrictionsClient->findMinimumStayForDate($restrictions, Carbon::parse('2025-12-25'), 'category-1'))->toBeNull();
});

it('applies open-ended restrictions with null startUtc and endUtc', function () {
    $restrictions = [
        Restriction::map([
            'Id' => 'restriction-1',
            'ServiceId' => 'service-1',
            'Conditions' => [
                'Type' => 'Stay',
                'ResourceCategoryId' => null,
                'StartUtc' => null,
                'EndUtc' => null,
                'Days' => [],
                'Hours' => [],
            ],
            'Exceptions' => ['MinLength' => 'P0M7DT0H0M0S'],
        ]),
    ];

    $restrictionsClient = new RestrictionsClient(new MewsHttpClient($this->config, Mockery::mock(Client::class)));

    // Any date, any category — restriction applies indefinitely to all categories
    expect($restrictionsClient->findMinimumStayForDate($restrictions, Carbon::parse('2030-06-15'), 'any-category'))->toBe(7);
});

it('chunks requests when date range exceeds 90 days', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);

    // Range of 180 days → 2 chunks of ≤90 days each
    $httpClient->shouldReceive('post')
        ->twice() // Exactly 2 API calls
        ->with(
            Mockery::pattern('#/api/connector/v1/restrictions/getAll#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                $start = Carbon::parse($body['CollidingUtc']['StartUtc']);
                $end = Carbon::parse($body['CollidingUtc']['EndUtc']);
                // Each chunk must be ≤90 days
                expect($end->diffInDays($start))->toBeLessThanOrEqual(89);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $restrictionsClient = new RestrictionsClient($mewsClient);

    $response = $restrictionsClient->getAll(
        serviceId: 'test-service',
        start: Carbon::parse('2025-01-01'),
        end: Carbon::parse('2025-06-29') // 179 days = 2 chunks
    );

    expect($response)->toBeInstanceOf(RestrictionsResponse::class);
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

    // Use a ≤90-day range so only 1 chunk is needed
    $response = $restrictionsClient->getAll(
        serviceId: 'test-service',
        start: Carbon::parse('2025-01-01'),
        end: Carbon::parse('2025-03-01') // 59 days — single chunk
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

    // Use a ≤90-day range so only 1 chunk is needed — tests per-chunk page limit
    $restrictionsClient->getAll(
        serviceId: 'test-service',
        start: Carbon::parse('2025-01-01'),
        end: Carbon::parse('2025-03-01') // 59 days — single chunk, hits 100-page limit
    );
})->throws(
    \Shelfwood\PhpPms\Mews\Exceptions\MewsApiException::class,
    'Restrictions pagination exceeded 100 pages'
);

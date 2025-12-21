<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Config\MewsConfig;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Clients\Production\PricingClient;
use Shelfwood\PhpPms\Mews\Clients\Production\AvailabilityClient;
use Shelfwood\PhpPms\Mews\Payloads\GetPricingPayload;
use Shelfwood\PhpPms\Mews\Responses\RatesResponse;
use Shelfwood\PhpPms\Mews\Responses\PricingResponse;
use Shelfwood\PhpPms\Mews\Responses\CalendarResponse;

beforeEach(function () {
    $this->config = new MewsConfig(
        clientToken: 'test_client_token',
        accessToken: 'test_access_token',
        baseUrl: 'https://api.mews-demo.com',
        clientName: 'TestClient/1.0'
    );

    // Load mock response data
    $this->ratesMockData = json_decode(
        file_get_contents(__DIR__ . '/../../../../mocks/mews/responses/rates-getall.json'),
        true
    );

    $this->pricingMockData = json_decode(
        file_get_contents(__DIR__ . '/../../../../mocks/mews/responses/rates-getpricing.json'),
        true
    );

    $this->availabilityMockData = json_decode(
        file_get_contents(__DIR__ . '/../../../../mocks/mews/responses/services-getavailability.json'),
        true
    );
});

it('gets all service rates successfully', function () {
    $mockResponse = new Response(200, [], json_encode($this->ratesMockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/rates/getAll#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ClientToken', 'AccessToken', 'ServiceIds'])
                    ->and($body['ServiceIds'])->toBeArray()
                    ->and($body['ServiceIds'])->toHaveCount(1);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $availabilityClient = new AvailabilityClient($mewsClient);
    $pricingClient = new PricingClient($mewsClient, $availabilityClient);

    $response = $pricingClient->getServiceRates('ec9d261c-1ef1-4a6e-8565-ad7200d77411');

    expect($response)->toBeInstanceOf(RatesResponse::class)
        ->and($response->rates)->toHaveCount(1)
        ->and($response->rates[0]->id)->toBe('11672368-e0d7-4a6d-bd85-ad7200d77428')
        ->and($response->rates[0]->name)->toBe('Fully Flexible')
        ->and($response->rates[0]->type)->toBe('Public')
        ->and($response->rates[0]->isActive)->toBeTrue()
        ->and($response->rates[0]->isPublic)->toBeTrue();
});

it('gets pricing for rate and date range successfully', function () {
    $mockResponse = new Response(200, [], json_encode($this->pricingMockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/rates/getPricing#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ClientToken', 'AccessToken', 'RateId', 'StartUtc', 'EndUtc']);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $availabilityClient = new AvailabilityClient($mewsClient);
    $pricingClient = new PricingClient($mewsClient, $availabilityClient);

    $payload = new GetPricingPayload(
        rateId: '11672368-e0d7-4a6d-bd85-ad7200d77428',
        firstTimeUnitStartUtc: Carbon::parse('2025-01-15'),
        lastTimeUnitStartUtc: Carbon::parse('2025-01-17'),
        occupancyConfiguration: ['AdultCount' => 2]
    );

    $response = $pricingClient->getPricing($payload);

    expect($response)->toBeInstanceOf(PricingResponse::class)
        ->and($response->currency)->toBe('EUR')
        ->and($response->timeUnitStartsUtc)->toHaveCount(3)
        ->and($response->baseAmountPrices)->toHaveCount(3)
        ->and($response->categoryPrices)->toHaveCount(1);
});

it('validates pricing response structure', function () {
    $mockResponse = new Response(200, [], json_encode($this->pricingMockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $availabilityClient = new AvailabilityClient($mewsClient);
    $pricingClient = new PricingClient($mewsClient, $availabilityClient);

    $payload = new GetPricingPayload(
        rateId: 'test-rate',
        firstTimeUnitStartUtc: Carbon::parse('2025-01-15'),
        lastTimeUnitStartUtc: Carbon::parse('2025-01-17')
    );

    $response = $pricingClient->getPricing($payload);

    // Validate base prices structure
    expect($response->baseAmountPrices[0]->index)->toBe(0)
        ->and($response->baseAmountPrices[0]->amount)->toHaveKeys(['grossValue', 'netValue', 'taxValues', 'currency'])
        ->and($response->baseAmountPrices[0]->amount['grossValue'])->toBe(150.00)
        ->and($response->baseAmountPrices[0]->amount['netValue'])->toBe(137.61);

    // Validate category prices structure
    expect($response->categoryPrices[0]->resourceCategoryId)->toBe('44bd8ad0-e70b-4bd9-8445-ad7200d7c349')
        ->and($response->categoryPrices[0]->amountPrices)->toHaveCount(3);
});

it('gets calendar data with availability and pricing', function () {
    $mockRatesResponse = new Response(200, [], json_encode($this->ratesMockData));
    $mockPricingResponse = new Response(200, [], json_encode($this->pricingMockData));
    $mockAvailabilityResponse = new Response(200, [], json_encode($this->availabilityMockData));

    $httpClient = Mockery::mock(Client::class);

    // First call: get availability
    $httpClient->shouldReceive('post')
        ->once()
        ->with(Mockery::pattern('#/services/getAvailability#'), Mockery::any())
        ->andReturn($mockAvailabilityResponse);

    // Second call: get rates
    $httpClient->shouldReceive('post')
        ->once()
        ->with(Mockery::pattern('#/rates/getAll#'), Mockery::any())
        ->andReturn($mockRatesResponse);

    // Third call: get pricing
    $httpClient->shouldReceive('post')
        ->once()
        ->with(Mockery::pattern('#/rates/getPricing#'), Mockery::any())
        ->andReturn($mockPricingResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $availabilityClient = new AvailabilityClient($mewsClient);
    $pricingClient = new PricingClient($mewsClient, $availabilityClient);

    $start = Carbon::parse('2025-01-15');
    $end = Carbon::parse('2025-01-17');

    $response = $pricingClient->getCalendar(
        serviceId: 'ec9d261c-1ef1-4a6e-8565-ad7200d77411',
        start: $start,
        end: $end,
        adults: 2,
        children: 0
    );

    expect($response)->toBeInstanceOf(CalendarResponse::class)
        ->and($response->availability)->not->toBeNull()
        ->and($response->pricing)->not->toBeNull()
        ->and($response->serviceId)->toBe('ec9d261c-1ef1-4a6e-8565-ad7200d77411')
        ->and($response->startUtc->toDateString())->toBe($start->toDateString())
        ->and($response->endUtc->toDateString())->toBe($end->toDateString());
});

it('gets calendar without pricing when no public rates exist', function () {
    // Mock rates response with no public rates
    $ratesWithoutPublic = $this->ratesMockData;
    $ratesWithoutPublic['Rates'][0]['Type'] = 'Private';

    $mockRatesResponse = new Response(200, [], json_encode($ratesWithoutPublic));
    $mockAvailabilityResponse = new Response(200, [], json_encode($this->availabilityMockData));

    $httpClient = Mockery::mock(Client::class);

    // First call: get availability
    $httpClient->shouldReceive('post')
        ->once()
        ->with(Mockery::pattern('#/services/getAvailability#'), Mockery::any())
        ->andReturn($mockAvailabilityResponse);

    // Second call: get rates
    $httpClient->shouldReceive('post')
        ->once()
        ->with(Mockery::pattern('#/rates/getAll#'), Mockery::any())
        ->andReturn($mockRatesResponse);

    // Should NOT call getPricing
    $httpClient->shouldNotReceive('post')
        ->with(Mockery::pattern('#/rates/getPricing#'), Mockery::any());

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $availabilityClient = new AvailabilityClient($mewsClient);
    $pricingClient = new PricingClient($mewsClient, $availabilityClient);

    $response = $pricingClient->getCalendar(
        serviceId: 'test-service',
        start: Carbon::parse('2025-01-15'),
        end: Carbon::parse('2025-01-17')
    );

    expect($response)->toBeInstanceOf(CalendarResponse::class)
        ->and($response->availability)->not->toBeNull()
        ->and($response->pricing)->toBeNull();
});

it('sends correct pricing request with guest counts', function () {
    $mockResponse = new Response(200, [], json_encode($this->pricingMockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                $body = $options['json'];

                // Verify adult/child counts are included
                expect($body)->toHaveKeys(['AdultCount', 'ChildCount'])
                    ->and($body['AdultCount'])->toBe(2)
                    ->and($body['ChildCount'])->toBe(1);

                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $availabilityClient = new AvailabilityClient($mewsClient);
    $pricingClient = new PricingClient($mewsClient, $availabilityClient);

    $payload = new GetPricingPayload(
        rateId: 'test-rate',
        firstTimeUnitStartUtc: Carbon::parse('2025-01-15'),
        lastTimeUnitStartUtc: Carbon::parse('2025-01-17'),
        occupancyConfiguration: ['AdultCount' => 2, 'ChildCount' => 1]
    );

    $pricingClient->getPricing($payload);
});

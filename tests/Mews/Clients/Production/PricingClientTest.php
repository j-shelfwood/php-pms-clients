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
use Shelfwood\PhpPms\Mews\Enums\RateType;

beforeEach(function () {
    $this->config = new MewsConfig(
        clientToken: 'test_client_token',
        accessToken: 'test_access_token',
        baseUrl: 'https://api.mews-demo.com',
        clientName: 'TestClient/1.0'
    );

    // Load mock response data
    $this->itemsMockData = json_decode(
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
    $mockResponse = new Response(200, [], json_encode($this->itemsMockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/rates/getAll#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ClientToken', 'AccessToken', 'ServiceIds', 'Limitation'])
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
        ->and($response->items)->toHaveCount(1)
        ->and($response->items[0]->id)->toBe('11672368-e0d7-4a6d-bd85-ad7200d77428')
        ->and($response->items[0]->names['en-GB'])->toBe('Fully Flexible')
        ->and($response->items[0]->type)->toBe(RateType::Public)
        ->and($response->items[0]->isActive)->toBeTrue()
        ->and($response->items[0]->isPublic)->toBeTrue();
});

it('gets pricing for rate and date range successfully', function () {
    $mockResponse = new Response(200, [], json_encode($this->pricingMockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(Mockery::pattern('#/api/connector/v1/configuration/get#'), Mockery::any())
        ->andReturn(new Response(200, [], json_encode([
            'Enterprise' => ['TimeZoneIdentifier' => 'Europe/Budapest'],
        ])));

    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/rates/getPricing#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ClientToken', 'AccessToken', 'RateId', 'FirstTimeUnitStartUtc', 'LastTimeUnitStartUtc']);
                // Enterprise-midnight boundaries (Budapest winter = UTC+1)
                expect($body['FirstTimeUnitStartUtc'])->toBe('2025-01-14T23:00:00Z')
                    ->and($body['LastTimeUnitStartUtc'])->toBe('2025-01-16T23:00:00Z');
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
        lastTimeUnitStartUtc: Carbon::parse('2025-01-17')
    );

    $response = $pricingClient->getPricing($payload);

    expect($response)->toBeInstanceOf(PricingResponse::class)
        ->and($response->currency)->toBe('GBP')
        ->and($response->timeUnitStartsUtc)->toHaveCount(4)
        ->and($response->baseAmountPrices)->toHaveCount(4)
        ->and($response->categoryPrices)->toHaveCount(4);
});

it('validates pricing response structure', function () {
    $mockResponse = new Response(200, [], json_encode($this->pricingMockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(Mockery::pattern('#/api/connector/v1/configuration/get#'), Mockery::any())
        ->andReturn(new Response(200, [], json_encode([
            'Enterprise' => ['TimeZoneIdentifier' => 'Europe/Budapest'],
        ])));

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
    expect($response->baseAmountPrices[0])->toBeInstanceOf(\Shelfwood\PhpPms\Mews\Responses\ValueObjects\AmountPrice::class)
        ->and($response->baseAmountPrices[0]->grossValue)->toEqual(10000.0)
        ->and($response->baseAmountPrices[0]->netValue)->toEqual(8333.33)
        ->and($response->baseAmountPrices[0]->currency)->toBe('GBP')
        ->and($response->baseAmountPrices[0]->taxValues)->toHaveCount(1)
        ->and($response->baseAmountPrices[0]->taxValues[0]->code)->toBe('UK-2022-20%')
        ->and($response->baseAmountPrices[0]->taxValues[0]->value)->toEqual(1666.67)
        ->and($response->baseAmountPrices[0]->breakdown)->not->toBeNull()
        ->and($response->baseAmountPrices[0]->breakdown->items[0]->taxRateCode)->toBe('UK-2022-20%')
        ->and($response->baseAmountPrices[0]->breakdown->items[0]->taxValue)->toEqual(1666.67);

    // Validate category prices structure
    expect($response->categoryPrices[0]->resourceCategoryId)->toBe('44bd8ad0-e70b-4bd9-8445-ad7200d7c349')
        ->and($response->categoryPrices[0]->amountPrices)->toHaveCount(4);
});

it('sends pricing request without occupancy configuration', function () {
    $mockResponse = new Response(200, [], json_encode($this->pricingMockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(Mockery::pattern('#/api/connector/v1/configuration/get#'), Mockery::any())
        ->andReturn(new Response(200, [], json_encode([
            'Enterprise' => ['TimeZoneIdentifier' => 'Europe/Budapest'],
        ])));

    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                $body = $options['json'];

                expect($body)->not->toHaveKey('OccupancyConfiguration');

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
        lastTimeUnitStartUtc: Carbon::parse('2025-01-17')
    );

    $pricingClient->getPricing($payload);
});

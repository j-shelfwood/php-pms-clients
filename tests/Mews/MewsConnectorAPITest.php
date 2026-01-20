<?php

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Shelfwood\PhpPms\Mews\Config\MewsConfig;
use Shelfwood\PhpPms\Mews\MewsConnectorAPI;
use Shelfwood\PhpPms\Mews\Enums\ReservationState;
use Shelfwood\PhpPms\Mews\Responses\AvailabilityResponse;
use Shelfwood\PhpPms\Mews\Responses\PricingResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Reservation;
use Shelfwood\PhpPms\Mews\Enums\ResourceAvailabilityMetricType;

beforeEach(function () {
    $this->config = new MewsConfig(
        clientToken: 'test_client_token',
        accessToken: 'test_access_token',
        baseUrl: 'https://api.mews-demo.com',
        clientName: 'TestClient/1.0'
    );
});

it('supports getAvailability with named parameters', function () {
    $mockData = json_decode(
        file_get_contents(__DIR__ . '/../../mocks/mews/responses/services-getavailability.json'),
        true
    );

    $mockResponse = new Response(200, [], json_encode($mockData));

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
            Mockery::pattern('#/api/connector/v1/services/getAvailability#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ClientToken', 'AccessToken', 'Client', 'ServiceId', 'FirstTimeUnitStartUtc', 'LastTimeUnitStartUtc', 'Metrics']);
                expect($body['FirstTimeUnitStartUtc'])->toMatch('/Z$/');
                expect($body['LastTimeUnitStartUtc'])->toMatch('/Z$/');
                expect($body['Metrics'])->toContain(ResourceAvailabilityMetricType::Occupied->value);
                // Enterprise-midnight boundaries (Budapest winter = UTC+1)
                expect($body['FirstTimeUnitStartUtc'])->toBe('2025-12-18T23:00:00Z')
                    ->and($body['LastTimeUnitStartUtc'])->toBe('2025-12-22T23:00:00Z');
                return true;
            })
        )
        ->andReturn($mockResponse);

    $api = new MewsConnectorAPI($this->config, $httpClient);

    $response = $api->getAvailability(
        'ec9d261c-1ef1-4a6e-8565-ad7200d77411',
        firstTimeUnitStartUtc: Carbon::parse('2025-12-19'),
        lastTimeUnitStartUtc: Carbon::parse('2025-12-23')
    );

    expect($response)->toBeInstanceOf(AvailabilityResponse::class);
});

it('supports getPricing with named parameters', function () {
    $mockData = json_decode(
        file_get_contents(__DIR__ . '/../../mocks/mews/responses/rates-getpricing.json'),
        true
    );

    $mockResponse = new Response(200, [], json_encode($mockData));

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
                expect($body)->toHaveKeys(['ClientToken', 'AccessToken', 'Client', 'RateId', 'FirstTimeUnitStartUtc', 'LastTimeUnitStartUtc']);
                expect($body)->not->toHaveKey('OccupancyConfiguration');
                // Enterprise-midnight boundaries (Budapest winter = UTC+1)
                expect($body['FirstTimeUnitStartUtc'])->toBe('2025-01-14T23:00:00Z')
                    ->and($body['LastTimeUnitStartUtc'])->toBe('2025-01-16T23:00:00Z');
                return true;
            })
        )
        ->andReturn($mockResponse);

    $api = new MewsConnectorAPI($this->config, $httpClient);

    $response = $api->getPricing(
        'ed4b660b-19d0-434b-9360-a4de2101ed08',
        firstTimeUnitStartUtc: Carbon::parse('2025-01-15'),
        lastTimeUnitStartUtc: Carbon::parse('2025-01-17')
    );

    expect($response)->toBeInstanceOf(PricingResponse::class);
});

it('creates reservation from params', function () {
    $mockData = json_decode(
        file_get_contents(__DIR__ . '/../../mocks/mews/responses/reservations-add.json'),
        true
    );

    $mockResponse = new Response(200, [], json_encode($mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/reservations/add#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ClientToken', 'AccessToken', 'Client', 'ServiceId', 'Reservations', 'SendConfirmationEmail']);
                expect($body['Reservations'])->toBeArray()->toHaveCount(1);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $api = new MewsConnectorAPI($this->config, $httpClient);

    $reservation = $api->createReservationFromParams(
        serviceId: 'bd26d8db-86a4-4f18-9e94-1b2362a1073c',
        customerId: '35d4b117-4e60-44a3-9580-c1deae0557c1',
        rateId: 'ed4b660b-19d0-434b-9360-a4de2101ed08',
        startUtc: Carbon::parse('2025-01-15'),
        endUtc: Carbon::parse('2025-01-18'),
        personCounts: [
            ['AgeCategoryId' => '1f67644f-052d-4863-acdf-ae1600c60ca0', 'Count' => 2],
        ],
        requestedCategoryId: '773d5e42-de1e-43a0-9ce6-c3e7511c1e0a',
        state: ReservationState::Confirmed,
        notes: 'Test',
        sendConfirmationEmail: true
    );

    expect($reservation)->toBeInstanceOf(Reservation::class);
});

it('updates reservation state via enum', function () {
    $mockData = json_decode(
        file_get_contents(__DIR__ . '/../../mocks/mews/responses/reservations-getall.json'),
        true
    );

    $updatedReservation = $mockData['Reservations'][0];
    $updatedReservation['State'] = 'Canceled';

    $mockResponse = new Response(200, [], json_encode([
        'Reservations' => [$updatedReservation],
    ]));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/reservations/update#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKey('ReservationUpdates');
                expect($body['ReservationUpdates'][0]['State'])->toBe('Canceled');
                return true;
            })
        )
        ->andReturn($mockResponse);

    $api = new MewsConnectorAPI($this->config, $httpClient);

    $reservation = $api->updateReservationState(
        reservationId: 'bfee2c44-1f84-4326-a862-5289598a6cea',
        newState: ReservationState::Canceled
    );

    expect($reservation)->toBeInstanceOf(Reservation::class)
        ->and($reservation->state)->toBe(ReservationState::Canceled);
});

it('fetches resource block by ID', function () {
    $mockData = json_decode(
        file_get_contents(__DIR__ . '/../../mocks/mews/responses/resourceblocks-get.json'),
        true
    );

    $mockResponse = new Response(200, [], json_encode($mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/resourceBlocks/get#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ClientToken', 'ServiceIds', 'ResourceBlockIds']);
                expect($body['ServiceIds'])->toContain('bd26d8db-86a4-4f18-9e94-1b2362a1073c');
                expect($body['ResourceBlockIds'])->toContain('7cccbdc6-73cf-4cd4-8056-6fd00f4d9699');
                return true;
            })
        )
        ->andReturn($mockResponse);

    $api = new MewsConnectorAPI($this->config, $httpClient);

    $block = $api->getResourceBlock(
        serviceId: 'bd26d8db-86a4-4f18-9e94-1b2362a1073c',
        blockId: '7cccbdc6-73cf-4cd4-8056-6fd00f4d9699'
    );

    expect($block)->toBeInstanceOf(\Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceBlock::class)
        ->and($block->id)->toBe('7cccbdc6-73cf-4cd4-8056-6fd00f4d9699')
        ->and($block->serviceId)->toBe('bd26d8db-86a4-4f18-9e94-1b2362a1073c')
        ->and($block->type)->toBe('OutOfOrder');
});

it('returns null when resource block not found', function () {
    $mockResponse = new Response(200, [], json_encode([
        'ResourceBlocks' => [],
    ]));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/resourceBlocks/get#'),
            Mockery::type('array')
        )
        ->andReturn($mockResponse);

    $api = new MewsConnectorAPI($this->config, $httpClient);

    $block = $api->getResourceBlock(
        serviceId: 'bd26d8db-86a4-4f18-9e94-1b2362a1073c',
        blockId: 'nonexistent-block-id'
    );

    expect($block)->toBeNull();
});

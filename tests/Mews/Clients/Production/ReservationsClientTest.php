<?php

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Shelfwood\PhpPms\Mews\Config\MewsConfig;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Clients\Production\ReservationsClient;
use Shelfwood\PhpPms\Mews\Payloads\CreateReservationPayload;
use Shelfwood\PhpPms\Mews\Payloads\UpdateReservationPayload;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Reservation;
use Shelfwood\PhpPms\Mews\Exceptions\MewsApiException;
use Shelfwood\PhpPms\Mews\Enums\ReservationState;

beforeEach(function () {
    $this->config = new MewsConfig(
        clientToken: 'test_client_token',
        accessToken: 'test_access_token',
        baseUrl: 'https://api.mews-demo.com',
        clientName: 'TestClient/1.0'
    );

    // Load mock response data
    $this->mockData = json_decode(
        file_get_contents(__DIR__ . '/../../../../mocks/mews/responses/reservations-getall.json'),
        true
    );
});

it('creates a new reservation successfully', function () {
    $mockResponse = new Response(200, [], json_encode([
        'Reservations' => [$this->mockData['Reservations'][0]]
    ]));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/reservations/add#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ClientToken', 'AccessToken', 'ServiceId', 'Reservations', 'SendConfirmationEmail']);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $reservationsClient = new ReservationsClient($mewsClient);

    $payload = new CreateReservationPayload(
        serviceId: 'bd26d8db-86a4-4f18-9e94-1b2362a1073c',
        customerId: '35d4b117-4e60-44a3-9580-c1deae0557c1',
        rateId: 'ed4b660b-19d0-434b-9360-a4de2101ed08',
        startUtc: Carbon::parse('2025-01-15'),
        endUtc: Carbon::parse('2025-01-18'),
        personCounts: [
            ['AgeCategoryId' => '1f67644f-052d-4863-acdf-ae1600c60ca0', 'Count' => 2]
        ],
        requestedCategoryId: '773d5e42-de1e-43a0-9ce6-c3e7511c1e0a',
        notes: 'Late check-in requested'
    );

    $reservation = $reservationsClient->create($payload, sendConfirmationEmail: true);

    expect($reservation)->toBeInstanceOf(Reservation::class)
        ->and($reservation->id)->toBe('bfee2c44-1f84-4326-a862-5289598a6cea')
        ->and($reservation->state)->toBe(ReservationState::Confirmed)
        ->and($reservation->number)->toBe('52');
});

it('throws exception when reservation creation fails', function () {
    $mockResponse = new Response(200, [], json_encode(['Reservations' => []]));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $reservationsClient = new ReservationsClient($mewsClient);

    $payload = new CreateReservationPayload(
        serviceId: 'test-service',
        customerId: 'test-customer',
        rateId: 'test-rate',
        startUtc: Carbon::parse('2025-01-15'),
        endUtc: Carbon::parse('2025-01-18'),
        personCounts: [
            ['AgeCategoryId' => 'test-age-category', 'Count' => 2]
        ]
    );

    $reservationsClient->create($payload);
})->throws(MewsApiException::class, 'Failed to create reservation');

it('gets reservation by ID successfully', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/reservations/getAll#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKey('ReservationIds')
                    ->and($body['ReservationIds'])->toBeArray()
                    ->and($body['ReservationIds'])->toHaveCount(1);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $reservationsClient = new ReservationsClient($mewsClient);

    $reservation = $reservationsClient->getById('bfee2c44-1f84-4326-a862-5289598a6cea');

    expect($reservation)->toBeInstanceOf(Reservation::class)
        ->and($reservation->id)->toBe('bfee2c44-1f84-4326-a862-5289598a6cea')
        ->and($reservation->state)->toBe(ReservationState::Confirmed);
});

it('throws exception when reservation not found by ID', function () {
    $mockResponse = new Response(200, [], json_encode(['Reservations' => [], 'Cursor' => null]));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $reservationsClient = new ReservationsClient($mewsClient);

    $reservationsClient->getById('non-existent-id');
})->throws(MewsApiException::class, 'Reservation not found');

it('gets all reservations for service and date range', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/reservations/getAll#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ServiceIds', 'FirstTimeUnitStartUtc', 'LastTimeUnitStartUtc'])
                    ->and($body['ServiceIds'])->toBeArray()
                    ->and($body['FirstTimeUnitStartUtc'])->toBeString()
                    ->and($body['LastTimeUnitStartUtc'])->toBeString();
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $reservationsClient = new ReservationsClient($mewsClient);

    $response = $reservationsClient->getAll(
        serviceId: 'bd26d8db-86a4-4f18-9e94-1b2362a1073c',
        startDate: Carbon::parse('2025-01-01'),
        endDate: Carbon::parse('2025-01-31')
    );

    expect($response->items)->toHaveCount(2)
        ->and($response->items[0])->toBeInstanceOf(Reservation::class)
        ->and($response->items[0]->state)->toBe(ReservationState::Confirmed)
        ->and($response->items[1]->state)->toBe(ReservationState::Started);
});

it('filters reservations by states', function () {
    $mockResponse = new Response(200, [], json_encode([
        'Reservations' => [$this->mockData['Reservations'][0]],
        'Cursor' => null
    ]));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::any(),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKey('ReservationStates')
                    ->and($body['ReservationStates'])->toBe(['Confirmed', 'Started']);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $reservationsClient = new ReservationsClient($mewsClient);

    $response = $reservationsClient->getAll(
        serviceId: 'test-service',
        startDate: Carbon::parse('2025-01-01'),
        endDate: Carbon::parse('2025-01-31'),
        states: ['Confirmed', 'Started']
    );

    expect($response->items)->toHaveCount(1)
        ->and($response->items[0]->state)->toBe(ReservationState::Confirmed);
});

it('updates reservation successfully', function () {
    $mockResponse = new Response(200, [], json_encode([
        'Reservations' => [$this->mockData['Reservations'][0]]
    ]));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/reservations/update#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKey('ReservationUpdates')
                    ->and($body['ReservationUpdates'])->toBeArray();
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $reservationsClient = new ReservationsClient($mewsClient);

    $payload = new UpdateReservationPayload(
        reservationId: 'bfee2c44-1f84-4326-a862-5289598a6cea',
        notes: 'Updated notes'
    );

    $reservation = $reservationsClient->update($payload);

    expect($reservation)->toBeInstanceOf(Reservation::class)
        ->and($reservation->id)->toBe('bfee2c44-1f84-4326-a862-5289598a6cea');
});

it('throws exception when update fails', function () {
    $mockResponse = new Response(200, [], json_encode(['Reservations' => []]));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $reservationsClient = new ReservationsClient($mewsClient);

    $payload = new UpdateReservationPayload(
        reservationId: 'test-id',
        notes: 'Test'
    );

    $reservationsClient->update($payload);
})->throws(MewsApiException::class, 'Failed to update reservation');

it('cancels reservation successfully', function () {
    $cancelledReservation = $this->mockData['Reservations'][0];
    $cancelledReservation['State'] = 'Canceled';
    $mockResponse = new Response(200, [], json_encode(['Reservations' => [$cancelledReservation]]));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $reservationsClient = new ReservationsClient($mewsClient);

    $reservation = $reservationsClient->cancel(
        reservationId: 'bfee2c44-1f84-4326-a862-5289598a6cea',
        reason: 'Guest requested cancellation'
    );

    expect($reservation)->toBeInstanceOf(Reservation::class)
        ->and($reservation->state)->toBe(ReservationState::Canceled);
});

it('updates reservation state successfully', function () {
    $confirmedReservation = $this->mockData['Reservations'][0];
    $confirmedReservation['State'] = 'Confirmed';
    $mockResponse = new Response(200, [], json_encode(['Reservations' => [$confirmedReservation]]));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $reservationsClient = new ReservationsClient($mewsClient);

    $reservation = $reservationsClient->updateState(
        reservationId: 'bfee2c44-1f84-4326-a862-5289598a6cea',
        newState: ReservationState::Confirmed
    );

    expect($reservation)->toBeInstanceOf(Reservation::class)
        ->and($reservation->state)->toBe(ReservationState::Confirmed);
});

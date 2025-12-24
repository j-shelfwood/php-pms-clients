<?php

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Shelfwood\PhpPms\Mews\Config\MewsConfig;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Clients\Production\ReservationsClient;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Reservation;
use Shelfwood\PhpPms\Mews\Enums\ReservationState;

beforeEach(function () {
    $this->config = new MewsConfig(
        clientToken: 'test_client_token',
        accessToken: 'test_access_token',
        baseUrl: 'https://api.mews-demo.com',
        clientName: 'TestClient/1.0'
    );

    $this->mockData = json_decode(
        file_get_contents(__DIR__ . '/../../../../mocks/mews/responses/reservations-getall-2023-06-06.json'),
        true
    );
});

it('maps reservations from getAll/2023-06-06 and populates extended fields', function () {
    $mockResponse = new Response(200, [], json_encode($this->mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/reservations/getAll/2023-06-06#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ServiceIds', 'Extent', 'CollidingUtc', 'Limitation']);
                expect($body['Extent'])->toBe(['Reservations' => true]);
                expect($body['CollidingUtc'])->toHaveKeys(['StartUtc', 'EndUtc']);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $client = new ReservationsClient($mewsClient);

    $response = $client->getAll(
        serviceId: 'be79543b-5c71-469b-80f0-afc80106468d',
        startDate: Carbon::parse('2024-12-01 00:00:00 UTC'),
        endDate: Carbon::parse('2024-12-31 00:00:00 UTC')
    );

    expect($response->items)->toHaveCount(1)
        ->and($response->items[0])->toBeInstanceOf(Reservation::class)
        ->and($response->items[0]->state)->toBe(ReservationState::Confirmed)
        ->and($response->items[0]->accountType)->toBe('Customer')
        ->and($response->items[0]->creatorProfileId)->toBeString()
        ->and($response->items[0]->qrCodeData)->toBe('ABC123');
});


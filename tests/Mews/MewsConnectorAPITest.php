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
            Mockery::pattern('#/api/connector/v1/resourceBlocks/getAll#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ClientToken', 'AccessToken', 'Client', 'ResourceBlockIds', 'CollidingUtc', 'Limitation']);
                expect($body['ResourceBlockIds'])->toContain('73dd4eb5-1c8e-48c1-9677-ae4500b918ab');
                expect($body['Limitation']['Count'])->toBe(1);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $api = new MewsConnectorAPI($this->config, $httpClient);

    $block = $api->getResourceBlock(
        blockId: '73dd4eb5-1c8e-48c1-9677-ae4500b918ab'
    );

    expect($block)->toBeInstanceOf(\Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceBlock::class)
        ->and($block->id)->toBe('73dd4eb5-1c8e-48c1-9677-ae4500b918ab')
        ->and($block->enterpriseId)->toBe('851df8c8-90f2-4c4a-8e01-a4fc46b25178')
        ->and($block->assignedResourceId)->toBe('aea0d575-0284-4958-b387-ab1300d8fa6b')
        ->and($block->type)->toBe('OutOfOrder')
        ->and($block->name)->toBe('Space block unit: Spatie room sdsdxc (28-02-26 - 27-02-27)');
});

it('returns null when resource block not found', function () {
    $mockResponse = new Response(200, [], json_encode([
        'ResourceBlocks' => [],
    ]));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/resourceBlocks/getAll#'),
            Mockery::type('array')
        )
        ->andReturn($mockResponse);

    $api = new MewsConnectorAPI($this->config, $httpClient);

    $block = $api->getResourceBlock(
        blockId: 'nonexistent-block-id'
    );

    expect($block)->toBeNull();
});

it('updates customer via payload', function () {
    $mockResponse = new Response(200, [], json_encode([
        'Id' => 'customer-123',
        'ChainId' => 'chain-123',
        'Number' => 'C-123',
        'FirstName' => 'Updated',
        'LastName' => 'User'
    ]));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/customers/update#'),
            Mockery::type('array')
        )
        ->andReturn($mockResponse);

    $api = new Shelfwood\PhpPms\Mews\MewsConnectorAPI($this->config, $httpClient);
    $payload = new Shelfwood\PhpPms\Mews\Payloads\UpdateCustomerPayload(
        customerId: 'customer-123',
        firstName: 'Updated',
        lastName: 'User'
    );

    $customer = $api->updateCustomer($payload);
    expect($customer->id)->toBe('customer-123');
});

it('adds external payment with required parameters', function () {
    $mockData = json_decode(
        file_get_contents(__DIR__ . '/../../mocks/mews/responses/payments-addexternal.json'),
        true
    );

    $mockResponse = new Response(200, [], json_encode($mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/payments/addExternal#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ClientToken', 'AccessToken', 'Client', 'AccountId', 'Amount']);
                expect($body['AccountId'])->toBe('35d4b117-4e60-44a3-9580-c582117eff98');
                expect($body['Amount'])->toBe(['Currency' => 'GBP', 'GrossValue' => 10000]);
                return true;
            })
        )
        ->andReturn($mockResponse);

    $api = new MewsConnectorAPI($this->config, $httpClient);

    $result = $api->addExternalPayment([
        'AccountId' => '35d4b117-4e60-44a3-9580-c582117eff98',
        'Amount' => [
            'Currency' => 'GBP',
            'GrossValue' => 10000
        ]
    ]);

    expect($result)->toBeArray()
        ->toHaveKey('ExternalPaymentId')
        ->and($result['ExternalPaymentId'])->toBe('4ee05b77-ae21-46e8-8418-ac1c009dfb2b');
});

it('adds external payment with all optional parameters', function () {
    $mockData = json_decode(
        file_get_contents(__DIR__ . '/../../mocks/mews/responses/payments-addexternal.json'),
        true
    );

    $mockResponse = new Response(200, [], json_encode($mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/payments/addExternal#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys([
                    'ClientToken', 'AccessToken', 'Client',
                    'AccountId', 'Amount', 'Type', 'ExternalIdentifier',
                    'ReservationId', 'Notes', 'EnterpriseId'
                ]);
                expect($body['Type'])->toBe('Cash');
                expect($body['ExternalIdentifier'])->toBe('stripe-pi_abc123');
                expect($body['ReservationId'])->toBe('reservation-uuid-123');
                expect($body['Notes'])->toBe('Stripe payment: pi_abc123');
                expect($body['EnterpriseId'])->toBe('enterprise-uuid-456');
                return true;
            })
        )
        ->andReturn($mockResponse);

    $api = new MewsConnectorAPI($this->config, $httpClient);

    $result = $api->addExternalPayment([
        'AccountId' => '35d4b117-4e60-44a3-9580-c582117eff98',
        'Amount' => [
            'Currency' => 'EUR',
            'GrossValue' => 15000
        ],
        'Type' => 'Cash',
        'ExternalIdentifier' => 'stripe-pi_abc123',
        'ReservationId' => 'reservation-uuid-123',
        'Notes' => 'Stripe payment: pi_abc123',
        'EnterpriseId' => 'enterprise-uuid-456'
    ]);

    expect($result)->toBeArray()
        ->toHaveKey('ExternalPaymentId');
});

it('adds external payment with BillId parameter', function () {
    $mockData = json_decode(
        file_get_contents(__DIR__ . '/../../mocks/mews/responses/payments-addexternal.json'),
        true
    );

    $mockResponse = new Response(200, [], json_encode($mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/payments/addExternal#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKey('BillId');
                expect($body['BillId'])->toBe('bill-uuid-789');
                return true;
            })
        )
        ->andReturn($mockResponse);

    $api = new MewsConnectorAPI($this->config, $httpClient);

    $result = $api->addExternalPayment([
        'AccountId' => '35d4b117-4e60-44a3-9580-c582117eff98',
        'Amount' => [
            'Currency' => 'EUR',
            'GrossValue' => 20000
        ],
        'BillId' => 'bill-uuid-789'
    ]);

    expect($result)->toBeArray()
        ->toHaveKey('ExternalPaymentId');
});

it('adds external payment with accounting category', function () {
    $mockData = json_decode(
        file_get_contents(__DIR__ . '/../../mocks/mews/responses/payments-addexternal.json'),
        true
    );

    $mockResponse = new Response(200, [], json_encode($mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/payments/addExternal#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKey('AccountingCategoryId');
                expect($body['AccountingCategoryId'])->toBe('category-uuid-999');
                return true;
            })
        )
        ->andReturn($mockResponse);

    $api = new MewsConnectorAPI($this->config, $httpClient);

    $result = $api->addExternalPayment([
        'AccountId' => '35d4b117-4e60-44a3-9580-c582117eff98',
        'Amount' => [
            'Currency' => 'USD',
            'GrossValue' => 5000
        ],
        'AccountingCategoryId' => 'category-uuid-999'
    ]);

    expect($result)->toBeArray()
        ->toHaveKey('ExternalPaymentId');
});

it('adds order with tourist tax item linked to reservation', function () {
    $mockData = [
        'OrderId' => 'order-uuid-123',
    ];

    $mockResponse = new Response(200, [], json_encode($mockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/orders/add#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ClientToken', 'AccessToken', 'Client', 'ServiceId', 'CustomerId', 'LinkedReservationId', 'Items']);
                expect($body['ServiceId'])->toBe('service-uuid-123');
                expect($body['CustomerId'])->toBe('customer-uuid-456');
                expect($body['LinkedReservationId'])->toBe('reservation-uuid-789');
                expect($body['Items'])->toHaveCount(1);
                expect($body['Items'][0])->toHaveKeys(['Name', 'UnitCount', 'UnitAmount']);
                expect($body['Items'][0]['Name'])->toContain('Tourist Tax');
                expect($body['Items'][0]['UnitCount'])->toBe(1);
                expect($body['Items'][0]['UnitAmount']['GrossValue'])->toBe(2700.0); // 27 EUR in cents
                expect($body['Items'][0]['UnitAmount']['Currency'])->toBe('EUR');
                return true;
            })
        )
        ->andReturn($mockResponse);

    $api = new MewsConnectorAPI($this->config, $httpClient);

    $result = $api->addOrder(
        serviceId: 'service-uuid-123',
        customerId: 'customer-uuid-456',
        linkedReservationId: 'reservation-uuid-789',
        items: [[
            'Name' => 'Tourist Tax - City of The Hague (€3/person/night × 3 persons × 3 nights)',
            'UnitCount' => 1,
            'UnitAmount' => [
                'GrossValue' => 2700.0, // 27 EUR in cents
                'Currency' => 'EUR',
            ],
        ]]
    );

    expect($result)->toBeArray()
        ->toHaveKey('OrderId')
        ->and($result['OrderId'])->toBe('order-uuid-123');
});


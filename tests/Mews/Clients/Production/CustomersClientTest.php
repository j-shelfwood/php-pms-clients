<?php

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Shelfwood\PhpPms\Mews\Config\MewsConfig;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Clients\Production\CustomersClient;
use Shelfwood\PhpPms\Mews\Payloads\SearchCustomersPayload;
use Shelfwood\PhpPms\Mews\Payloads\CreateCustomerPayload;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Customer;
use Shelfwood\PhpPms\Mews\Exceptions\MewsApiException;

beforeEach(function () {
    $this->config = new MewsConfig(
        clientToken: 'test_client_token',
        accessToken: 'test_access_token',
        baseUrl: 'https://api.mews-demo.com',
        clientName: 'TestClient/1.0'
    );

    // Load mock response data
    $this->searchMockData = json_decode(
        file_get_contents(__DIR__ . '/../../../../mocks/mews/responses/customers-search.json'),
        true
    );

    $this->addMockData = json_decode(
        file_get_contents(__DIR__ . '/../../../../mocks/mews/responses/customers-add.json'),
        true
    );
});

it('searches for customers by email', function () {
    $mockResponse = new Response(200, [], json_encode($this->searchMockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/customers/getAll#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['ClientToken', 'AccessToken', 'Emails', 'Extent', 'Limitation'])
                    ->and($body['Emails'])->toBeArray()
                    ->and($body['Limitation'])->toHaveKey('Count');
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $customersClient = new CustomersClient($mewsClient);

    $payload = new SearchCustomersPayload(emails: ['john.smith@gmail.com']);
    $response = $customersClient->search($payload);

    expect($response->items->count())->toBeGreaterThan(0)
        ->and($response->items[0])->toBeInstanceOf(Customer::class)
        ->and($response->items[0]->email)->toBe('john.smith@gmail.com')
        ->and($response->items[0]->firstName)->toBe('John')
        ->and($response->items[0]->lastName)->toBe('Smith');
});

it('returns empty results when no customers match search', function () {
    $mockResponse = new Response(200, [], json_encode(['Customers' => []]));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $customersClient = new CustomersClient($mewsClient);

    $payload = new SearchCustomersPayload(emails: ['nonexistent@example.com']);
    $response = $customersClient->search($payload);

    expect($response->items)->toBeEmpty();
});

it('gets customer by ID successfully', function () {
    $mockResponse = new Response(200, [], json_encode($this->searchMockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/api/connector/v1/customers/getAll#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKey('CustomerIds')
                    ->and($body['CustomerIds'])->toBeArray()
                    ->and($body['CustomerIds'])->toHaveCount(1)
                    ->and($body)->toHaveKey('Extent')
                    ->and($body)->toHaveKey('Limitation');
                return true;
            })
        )
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $customersClient = new CustomersClient($mewsClient);

    $customer = $customersClient->getById('41a142b2-705c-4c2b-9ebb-0501d1665f3a');

    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->id)->toBe('41a142b2-705c-4c2b-9ebb-0501d1665f3a')
        ->and($customer->firstName)->toBe('John')
        ->and($customer->lastName)->toBe('Smith');
});

it('throws exception when customer not found by ID', function () {
    $mockResponse = new Response(200, [], json_encode(['Customers' => []]));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->andReturn($mockResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $customersClient = new CustomersClient($mewsClient);

    $customersClient->getById('non-existent-id');
})->throws(MewsApiException::class, 'Customer not found');

it('finds existing customer without creating new one', function () {
    $mockSearchResponse = new Response(200, [], json_encode($this->searchMockData));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->once()
        ->with(Mockery::pattern('#/customers/getAll#'), Mockery::any())
        ->andReturn($mockSearchResponse);

    // Should NOT call customers/add since customer exists
    $httpClient->shouldNotReceive('post')
        ->with(Mockery::pattern('#/customers/add#'), Mockery::any());

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $customersClient = new CustomersClient($mewsClient);

    $payload = new CreateCustomerPayload(
        firstName: 'John',
        lastName: 'Smith',
        email: 'john.smith@gmail.com'
    );

    $customerId = $customersClient->findOrCreate($payload);

    expect($customerId)->toBe('41a142b2-705c-4c2b-9ebb-0501d1665f3a');
});

it('creates new customer when not found', function () {
    $mockSearchResponse = new Response(200, [], json_encode(['Customers' => []]));
    $mockAddResponse = new Response(200, [], json_encode($this->addMockData));

    $httpClient = Mockery::mock(Client::class);

    // First call: search returns empty
    $httpClient->shouldReceive('post')
        ->once()
        ->with(Mockery::pattern('#/customers/getAll#'), Mockery::any())
        ->andReturn($mockSearchResponse);

    // Second call: add creates new customer
    $httpClient->shouldReceive('post')
        ->once()
        ->with(
            Mockery::pattern('#/customers/add#'),
            Mockery::on(function ($options) {
                $body = $options['json'];
                expect($body)->toHaveKeys(['FirstName', 'LastName', 'Email', 'OverwriteExisting'])
                    ->and($body['OverwriteExisting'])->toBeBool();
                return true;
            })
        )
        ->andReturn($mockAddResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $customersClient = new CustomersClient($mewsClient);

    $payload = new CreateCustomerPayload(
        firstName: 'Jane',
        lastName: 'Smith',
        email: 'jane.smith@example.com'
    );

    $customerId = $customersClient->findOrCreate($payload);

    expect($customerId)->toBe('8a3c8f42-1e95-4e3b-a7c9-82bca5a2d610');
});

it('throws exception when customer creation fails', function () {
    $mockSearchResponse = new Response(200, [], json_encode(['Customers' => []]));
    // Mews API returns a single customer object directly, not wrapped in Customers array
    // When creation fails, the response might be missing the Id field
    $mockAddResponse = new Response(200, [], json_encode(['Error' => 'Invalid request']));

    $httpClient = Mockery::mock(Client::class);
    $httpClient->shouldReceive('post')
        ->with(Mockery::pattern('#/customers/getAll#'), Mockery::any())
        ->andReturn($mockSearchResponse);

    $httpClient->shouldReceive('post')
        ->with(Mockery::pattern('#/customers/add#'), Mockery::any())
        ->andReturn($mockAddResponse);

    $mewsClient = new MewsHttpClient($this->config, $httpClient);
    $customersClient = new CustomersClient($mewsClient);

    $payload = new CreateCustomerPayload(
        firstName: 'Test',
        lastName: 'User',
        email: 'test@example.com'
    );

    $customersClient->findOrCreate($payload);
})->throws(MewsApiException::class, 'Failed to create customer: Invalid API response');

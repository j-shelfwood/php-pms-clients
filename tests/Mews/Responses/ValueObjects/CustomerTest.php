<?php

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Customer;

it('maps customer from API response', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/customers-search.json';
    $mockData = json_decode(file_get_contents($mockPath), true);
    $customerData = $mockData['Customers'][0];

    $customer = Customer::map($customerData);

    expect($customer->id)->toBe('35d4b117-4e60-44a3-9580-c1deae0557c1')
        ->and($customer->firstName)->toBe('John')
        ->and($customer->lastName)->toBe('Doe')
        ->and($customer->email)->toBe('john.doe@example.com')
        ->and($customer->isActive)->toBeTrue();
});

it('throws exception on missing required field', function () {
    Customer::map(['Id' => 'test-id']);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);

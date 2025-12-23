<?php

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Customer;

it('maps customer from API response', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/customers-search.json';
    $mockData = json_decode(file_get_contents($mockPath), true);
    $customerData = $mockData['Customers'][0]['Customer'];

    $customer = Customer::map($customerData);

    expect($customer->id)->toBe('41a142b2-705c-4c2b-9ebb-0501d1665f3a')
        ->and($customer->chainId)->toBe('8ddea57b-6a5c-4eec-8c4c-24467dce118e')
        ->and($customer->firstName)->toBe('John')
        ->and($customer->lastName)->toBe('Smith')
        ->and($customer->email)->toBe('john.smith@gmail.com')
        ->and($customer->isActive)->toBeTrue();
});

it('throws exception on missing required field', function () {
    Customer::map(['Id' => 'test-id']);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);

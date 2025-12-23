<?php

use Illuminate\Support\Collection;
use Shelfwood\PhpPms\Mews\Responses\CustomersResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Customer;

it('maps customers response from API', function () {
    $mockPath = __DIR__ . '/../../../mocks/mews/responses/customers-search.json';
    $mockData = json_decode(file_get_contents($mockPath), true);

    $response = CustomersResponse::map($mockData);

    expect($response->items)->toBeInstanceOf(Collection::class)
        ->and($response->items->count())->toBeGreaterThan(0)
        ->and($response->items[0])->toBeInstanceOf(Customer::class)
        ->and($response->items[0]->firstName)->toBe('John')
        ->and($response->cursor)->toBeNull();
});

it('handles empty customers', function () {
    $response = CustomersResponse::map(['Customers' => []]);

    expect($response->items)->toBeEmpty();
});

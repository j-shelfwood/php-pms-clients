<?php

use Shelfwood\PhpPms\Mews\Responses\CustomersResponse;

it('maps customers response from API', function () {
    $mockPath = __DIR__ . '/../../../mocks/mews/responses/customers-search.json';
    $mockData = json_decode(file_get_contents($mockPath), true);

    $response = CustomersResponse::map($mockData);

    expect($response->items)->toHaveCount(1)
        ->and($response->items[0]->firstName)->toBe('John')
        ->and($response->cursor)->toBeNull();
});

it('handles empty customers', function () {
    $response = CustomersResponse::map(['Customers' => []]);

    expect($response->items)->toBeEmpty();
});

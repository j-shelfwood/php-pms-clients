<?php

use Illuminate\Support\Collection;
use Shelfwood\PhpPms\Mews\Enums\RateType;
use Shelfwood\PhpPms\Mews\Responses\RatesResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Rate;

it('maps rates response from API', function () {
    $mockPath = __DIR__ . '/../../../mocks/mews/responses/rates-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);

    $response = RatesResponse::map($mockData);

    expect($response->items)->toBeInstanceOf(Collection::class)
        ->and($response->items)->toHaveCount(1)
        ->and($response->items[0])->toBeInstanceOf(Rate::class)
        ->and($response->items[0]->type)->toBe(RateType::Public)
        ->and($response->rateGroups)->toBeArray()
        ->and($response->cursor)->toBeNull();
});

it('handles empty rates', function () {
    $response = RatesResponse::map(['Rates' => []]);

    expect($response->items)->toBeEmpty();
});

<?php

use Shelfwood\PhpPms\Mews\Enums\RestrictionType;
use Shelfwood\PhpPms\Mews\Responses\RestrictionsResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Restriction;

it('maps restrictions response from API', function () {
    $mockPath = __DIR__ . '/../../../mocks/mews/responses/restrictions-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);

    $response = RestrictionsResponse::map($mockData);

    expect($response->items)->toHaveCount(1)
        ->and($response->items[0])->toBeInstanceOf(Restriction::class)
        ->and($response->items[0]->id)->toBe('b51c6a3a-48cd-4d81-a53e-b3b600af812e')
        ->and($response->items[0]->serviceId)->toBe('ec9d261c-1ef1-4a6e-8565-ad7200d77411')
        ->and($response->items[0]->conditions->type)->toBe(RestrictionType::Start)
        ->and($response->items[0]->exceptions->minLength)->toBe('P0M3DT0H0M0S')
        ->and($response->cursor)->toBeNull();
});

it('handles empty restrictions', function () {
    $response = RestrictionsResponse::map(['Restrictions' => []]);

    expect($response->items)->toBeEmpty();
});

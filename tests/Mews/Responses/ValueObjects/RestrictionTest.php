<?php

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Restriction;

it('maps restriction from API response', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/restrictions-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);
    $restrictionData = $mockData['Restrictions'][0];

    $restriction = Restriction::map($restrictionData);

    expect($restriction->id)->toBe('d4e5f6a7-8b9c-0d1e-2f3a-4b5c6d7e8f9a')
        ->and($restriction->type)->toBe('Stay')
        ->and($restriction->minimumStay)->toBe(3)
        ->and($restriction->conditions)->toBeArray();
});

it('throws exception on missing required field', function () {
    Restriction::map(['Type' => 'Stay']);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);

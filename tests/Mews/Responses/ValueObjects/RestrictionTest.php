<?php

use Shelfwood\PhpPms\Mews\Enums\RestrictionType;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Restriction;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\RestrictionConditions;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\RestrictionExceptions;

it('maps restriction from API response with all fields', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/restrictions-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);
    $restrictionData = $mockData['Restrictions'][0];

    $restriction = Restriction::map($restrictionData);

    // Top-level fields
    expect($restriction->id)->toBe('b51c6a3a-48cd-4d81-a53e-b3b600af812e')
        ->and($restriction->serviceId)->toBe('ec9d261c-1ef1-4a6e-8565-ad7200d77411')
        ->and($restriction->externalIdentifier)->toBeNull()
        ->and($restriction->origin)->toBe('Integration')
        ->and($restriction->createdUtc)->toBeString()
        ->and($restriction->updatedUtc)->toBeString();

    // Conditions object
    expect($restriction->conditions)->toBeInstanceOf(RestrictionConditions::class)
        ->and($restriction->conditions->type)->toBe(RestrictionType::Start)
        ->and($restriction->conditions->exactRateId)->toBe('11672368-e0d7-4a6d-bd85-ad7200d77428')
        ->and($restriction->conditions->baseRateId)->toBeNull()
        ->and($restriction->conditions->rateGroupId)->toBeNull()
        ->and($restriction->conditions->resourceCategoryId)->toBe('895d7917-ce46-4984-957f-b203008b442c')
        ->and($restriction->conditions->resourceCategoryType)->toBeNull()
        ->and($restriction->conditions->startUtc)->toBe('2026-01-02T23:00:00Z')
        ->and($restriction->conditions->endUtc)->toBe('2026-01-03T23:00:00Z')
        ->and($restriction->conditions->days)->toBeArray()
        ->and($restriction->conditions->days)->toHaveCount(3)
        ->and($restriction->conditions->days)->toContain('Friday', 'Saturday', 'Sunday')
        ->and($restriction->conditions->hours)->toBeArray()
        ->and($restriction->conditions->hours)->toHaveKey('Zero')
        ->and($restriction->conditions->hours['Zero'])->toBeTrue()
        ->and($restriction->conditions->hours)->toHaveKey('TwentyThree')
        ->and($restriction->conditions->hours['TwentyThree'])->toBeTrue();

    // Exceptions object
    expect($restriction->exceptions)->toBeInstanceOf(RestrictionExceptions::class)
        ->and($restriction->exceptions->minAdvance)->toBeNull()
        ->and($restriction->exceptions->maxAdvance)->toBeNull()
        ->and($restriction->exceptions->minLength)->toBe('P0M3DT0H0M0S')
        ->and($restriction->exceptions->maxLength)->toBeNull()
        ->and($restriction->exceptions->minPrice)->toBeNull()
        ->and($restriction->exceptions->maxPrice)->toBeNull()
        ->and($restriction->exceptions->minReservationCount)->toBeNull()
        ->and($restriction->exceptions->maxReservationCount)->toBeNull();
});

it('maps restriction conditions correctly', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/restrictions-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);
    $conditionsData = $mockData['Restrictions'][0]['Conditions'];

    $conditions = RestrictionConditions::map($conditionsData);

    expect($conditions->type)->toBe(RestrictionType::Start)
        ->and($conditions->exactRateId)->toBe('11672368-e0d7-4a6d-bd85-ad7200d77428')
        ->and($conditions->resourceCategoryId)->toBe('895d7917-ce46-4984-957f-b203008b442c')
        ->and($conditions->startUtc)->toBe('2026-01-02T23:00:00Z')
        ->and($conditions->endUtc)->toBe('2026-01-03T23:00:00Z')
        ->and($conditions->days)->toHaveCount(3)
        ->and($conditions->hours)->toHaveCount(24);
});

it('maps restriction exceptions correctly', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/restrictions-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);
    $exceptionsData = $mockData['Restrictions'][0]['Exceptions'];

    $exceptions = RestrictionExceptions::map($exceptionsData);

    expect($exceptions->minLength)->toBe('P0M3DT0H0M0S')
        ->and($exceptions->maxLength)->toBeNull()
        ->and($exceptions->minAdvance)->toBeNull()
        ->and($exceptions->maxAdvance)->toBeNull()
        ->and($exceptions->minPrice)->toBeNull()
        ->and($exceptions->maxPrice)->toBeNull();
});

it('throws exception on missing required field', function () {
    Restriction::map(['ServiceId' => 'test']);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);

it('throws exception on missing conditions', function () {
    Restriction::map([
        'Id' => 'test-id',
        'ServiceId' => 'test-service',
        'Exceptions' => []
    ]);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);

it('throws exception on missing exceptions', function () {
    Restriction::map([
        'Id' => 'test-id',
        'ServiceId' => 'test-service',
        'Conditions' => ['Type' => 'Start', 'StartUtc' => '2026-01-02T23:00:00Z', 'EndUtc' => '2026-01-03T23:00:00Z']
    ]);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);

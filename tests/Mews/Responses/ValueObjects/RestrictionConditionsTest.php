<?php

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\RestrictionConditions;
use Shelfwood\PhpPms\Mews\Enums\RestrictionType;

it('maps restriction conditions with required fields', function () {
    $conditions = RestrictionConditions::map([
        'Type' => 'Start',
        'StartUtc' => '2026-01-02T23:00:00Z',
        'EndUtc' => '2026-01-03T23:00:00Z',
        'Days' => ['Friday'],
        'Hours' => ['Zero' => true],
    ]);

    expect($conditions->type)->toBe(RestrictionType::Start)
        ->and($conditions->startUtc)->toBe('2026-01-02T23:00:00Z')
        ->and($conditions->endUtc)->toBe('2026-01-03T23:00:00Z');
});

it('throws on missing required fields', function () {
    RestrictionConditions::map([
        'StartUtc' => '2026-01-02T23:00:00Z',
        'EndUtc' => '2026-01-03T23:00:00Z',
        'Days' => ['Friday'],
        'Hours' => ['Zero' => true],
    ]);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);



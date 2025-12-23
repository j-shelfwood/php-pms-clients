<?php

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\AvailabilityBlock;

it('maps availability block from API response', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/services-getavailability.json';
    $mockData = json_decode(file_get_contents($mockPath), true);
    $blockData = $mockData['CategoryAvailabilities'][0];

    $block = AvailabilityBlock::map($blockData);

    expect($block->categoryId)->toBe('44bd8ad0-e70b-4bd9-8445-ad7200d7c349')
        ->and($block->availabilities)->toBe([6, 6, 6, 6, 6, 6])
        ->and($block->adjustments)->toBe([0, 0, 0, 0, 0, 0]);
});

it('throws exception on missing required field', function () {
    AvailabilityBlock::map(['Availabilities' => []]);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);

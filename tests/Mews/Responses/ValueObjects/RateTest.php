<?php

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Rate;

it('maps rate from API response', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/rates-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);
    $rateData = $mockData['Rates'][0];

    $rate = Rate::map($rateData);

    expect($rate->id)->toBe('11672368-e0d7-4a6d-bd85-ad7200d77428')
        ->and($rate->isActive)->toBeTrue()
        ->and($rate->type)->toBe('Public')
        ->and($rate->names)->toHaveKey('en-GB');
});

it('throws exception on missing required field', function () {
    Rate::map(['IsActive' => true]);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);

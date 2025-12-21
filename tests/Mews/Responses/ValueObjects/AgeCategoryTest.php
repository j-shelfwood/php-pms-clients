<?php

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\AgeCategory;

it('maps age category from API response', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/agecategories-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);
    $ageCategoryData = $mockData['AgeCategories'][0];

    $ageCategory = AgeCategory::map($ageCategoryData);

    expect($ageCategory->id)->toBe('d39dcfc0-69c5-43fe-b28e-ade3011a680a')
        ->and($ageCategory->classification)->toBe('Adult')
        ->and($ageCategory->minimalAge)->toBe(0)
        ->and($ageCategory->isActive)->toBeTrue();
});

it('throws exception on missing required field', function () {
    AgeCategory::map(['IsActive' => true]);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);

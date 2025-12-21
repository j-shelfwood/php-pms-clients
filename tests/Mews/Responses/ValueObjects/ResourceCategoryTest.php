<?php

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceCategory;

it('maps resource category from API response', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/resourcecategories-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);
    $categoryData = $mockData['ResourceCategories'][0];

    $category = ResourceCategory::map($categoryData);

    expect($category->id)->toBe('44bd8ad0-e70b-4bd9-8445-ad7200d7c349')
        ->and($category->type)->toBe('Room')
        ->and($category->capacity)->toBe(80)
        ->and($category->isActive)->toBeTrue();
});

it('throws exception on missing required field', function () {
    ResourceCategory::map(['IsActive' => true]);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);

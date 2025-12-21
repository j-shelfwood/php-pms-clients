<?php

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceCategoryAssignment;

it('maps resource category assignment from API response', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/resourcecategoryassignments-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);
    $assignmentData = $mockData['ResourceCategoryAssignments'][0];

    $assignment = ResourceCategoryAssignment::map($assignmentData);

    expect($assignment->id)->toBe('e1a2b3c4-5d6e-7f8a-9b0c-1d2e3f4a5b6c')
        ->and($assignment->resourceId)->toBe('095a6d7f-4893-4a3b-9c35-ff595d4bfa0c')
        ->and($assignment->categoryId)->toBe('44bd8ad0-e70b-4bd9-8445-ad7200d7c349')
        ->and($assignment->isActive)->toBeTrue();
});

it('throws exception on missing required field', function () {
    ResourceCategoryAssignment::map(['IsActive' => true]);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);

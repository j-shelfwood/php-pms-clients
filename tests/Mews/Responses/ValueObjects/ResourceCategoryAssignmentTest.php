<?php

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceCategoryAssignment;

it('maps resource category assignment from API response', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/resourcecategoryassignment-getforresource.json';
    $assignmentData = json_decode(file_get_contents($mockPath), true);

    $assignment = ResourceCategoryAssignment::map($assignmentData);

    expect($assignment->id)->toBe('abc12345-6789-0def-1234-567890abcdef')
        ->and($assignment->resourceId)->toBe('095a6d7f-4893-4a3b-9c35-ff595d4bfa0c')
        ->and($assignment->categoryId)->toBe('773d5e42-de1e-43a0-9ce6-c3e7511c1e0a')
        ->and($assignment->isActive)->toBeTrue();
});

it('throws exception on missing required field', function () {
    ResourceCategoryAssignment::map(['IsActive' => true]);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);

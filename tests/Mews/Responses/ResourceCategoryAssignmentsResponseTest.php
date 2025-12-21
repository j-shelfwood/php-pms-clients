<?php

use Shelfwood\PhpPms\Mews\Responses\ResourceCategoryAssignmentsResponse;

it('maps resource category assignments response from API', function () {
    $mockPath = __DIR__ . '/../../../mocks/mews/responses/resourcecategoryassignments-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);

    $response = ResourceCategoryAssignmentsResponse::map($mockData);

    expect($response->items)->toHaveCount(1)
        ->and($response->items[0]->isActive)->toBeTrue()
        ->and($response->cursor)->toBeNull();
});

it('handles empty resource category assignments', function () {
    $response = ResourceCategoryAssignmentsResponse::map(['ResourceCategoryAssignments' => []]);

    expect($response->items)->toBeEmpty();
});

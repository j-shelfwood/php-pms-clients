<?php

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Resource;
use Shelfwood\PhpPms\Mews\Enums\ResourceState;

it('maps resource from API response', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/resources-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);
    $resourceData = $mockData['Resources'][0];

    $resource = Resource::map($resourceData);

    expect($resource->id)->toBe('68aa4760-1b63-452e-9060-b32501247b08')
        ->and($resource->name)->toBe('0. Sirius ')
        ->and($resource->isActive)->toBeTrue()
        ->and($resource->state)->toBe(ResourceState::Dirty);
});

it('throws exception on missing required field', function () {
    Resource::map(['IsActive' => true]);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);

<?php

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Resource;

it('maps resource from API response', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/resources-getall.json';
    $mockData = json_decode(file_get_contents($mockPath), true);
    $resourceData = $mockData['Resources'][0];

    $resource = Resource::map($resourceData);

    expect($resource->id)->toBe('095a6d7f-4893-4a3b-9c35-ff595d4bfa0c')
        ->and($resource->name)->toBe('Updated Updated Updated Updated Updated')
        ->and($resource->isActive)->toBeTrue()
        ->and($resource->state)->toBe('Clean');
});

it('throws exception on missing required field', function () {
    Resource::map(['IsActive' => true]);
})->throws(\Shelfwood\PhpPms\Exceptions\MappingException::class);

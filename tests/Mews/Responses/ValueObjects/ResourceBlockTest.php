<?php

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceBlock;

it('creates resource block from API response', function () {
    $data = [
        'Id' => '73dd4eb5-1c8e-48c1-9677-ae4500b918ab',
        'EnterpriseId' => '851df8c8-90f2-4c4a-8e01-a4fc46b25178',
        'AssignedResourceId' => 'aea0d575-0284-4958-b387-ab1300d8fa6b',
        'IsActive' => true,
        'Type' => 'OutOfOrder',
        'StartUtc' => '2026-02-28T12:00:00Z',
        'EndUtc' => '2027-02-27T12:00:00Z',
        'CreatedUtc' => '2022-02-23T11:13:54Z',
        'UpdatedUtc' => '2022-02-23T11:13:54Z',
        'DeletedUtc' => null,
        'Name' => 'Space block unit: Spatie room sdsdxc (28-02-26 - 27-02-27)',
        'Notes' => 'Created with space',
    ];

    $block = ResourceBlock::fromApiResponse($data);

    expect($block->id)->toBe('73dd4eb5-1c8e-48c1-9677-ae4500b918ab')
        ->and($block->enterpriseId)->toBe('851df8c8-90f2-4c4a-8e01-a4fc46b25178')
        ->and($block->assignedResourceId)->toBe('aea0d575-0284-4958-b387-ab1300d8fa6b')
        ->and($block->isActive)->toBeTrue()
        ->and($block->type)->toBe('OutOfOrder')
        ->and($block->startUtc)->toBeInstanceOf(Carbon::class)
        ->and($block->startUtc->toIso8601String())->toBe('2026-02-28T12:00:00+00:00')
        ->and($block->endUtc)->toBeInstanceOf(Carbon::class)
        ->and($block->endUtc->toIso8601String())->toBe('2027-02-27T12:00:00+00:00')
        ->and($block->createdUtc)->toBeInstanceOf(Carbon::class)
        ->and($block->updatedUtc)->toBeInstanceOf(Carbon::class)
        ->and($block->deletedUtc)->toBeNull()
        ->and($block->name)->toBe('Space block unit: Spatie room sdsdxc (28-02-26 - 27-02-27)')
        ->and($block->notes)->toBe('Created with space');
});

it('handles resource block with null notes', function () {
    $data = [
        'Id' => '655b4f11-faf6-4a59-82d6-ae4500b918ab',
        'EnterpriseId' => '851df8c8-90f2-4c4a-8e01-a4fc46b25178',
        'AssignedResourceId' => 'aea0d575-0284-4958-b387-ab1300d8fa6b',
        'IsActive' => true,
        'Type' => 'OutOfOrder',
        'StartUtc' => '2025-03-01T12:00:00Z',
        'EndUtc' => '2026-02-28T12:00:00Z',
        'CreatedUtc' => '2022-02-23T11:13:54Z',
        'UpdatedUtc' => '2022-02-23T11:13:54Z',
        'DeletedUtc' => null,
        'Name' => 'Test block without notes',
        'Notes' => null,
    ];

    $block = ResourceBlock::fromApiResponse($data);

    expect($block->id)->toBe('655b4f11-faf6-4a59-82d6-ae4500b918ab')
        ->and($block->enterpriseId)->toBe('851df8c8-90f2-4c4a-8e01-a4fc46b25178')
        ->and($block->isActive)->toBeTrue()
        ->and($block->notes)->toBeNull();
});

it('parses resource blocks from mock API response', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/resourceblocks-get.json';
    $mockData = json_decode(file_get_contents($mockPath), true);
    $blockData = $mockData['ResourceBlocks'][0];

    $block = ResourceBlock::fromApiResponse($blockData);

    expect($block->id)->toBe('73dd4eb5-1c8e-48c1-9677-ae4500b918ab')
        ->and($block->enterpriseId)->toBe('851df8c8-90f2-4c4a-8e01-a4fc46b25178')
        ->and($block->assignedResourceId)->toBe('aea0d575-0284-4958-b387-ab1300d8fa6b')
        ->and($block->type)->toBe('OutOfOrder')
        ->and($block->name)->toBe('Space block unit: Spatie room sdsdxc (28-02-26 - 27-02-27)')
        ->and($block->notes)->toBe('Created with space');
});

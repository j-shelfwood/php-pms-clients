<?php

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceBlock;

it('creates resource block from API response', function () {
    $data = [
        'Id' => '7cccbdc6-73cf-4cd4-8056-6fd00f4d9699',
        'ServiceId' => 'bd26d8db-86a4-4f18-9e94-1b2362a1073c',
        'AssignedResourceId' => '68aa4760-1b63-452e-9060-b32501247b08',
        'StartUtc' => '2025-01-20T14:00:00Z',
        'EndUtc' => '2025-01-25T10:00:00Z',
        'Type' => 'OutOfOrder',
        'ReservationId' => null,
    ];

    $block = ResourceBlock::fromApiResponse($data);

    expect($block->id)->toBe('7cccbdc6-73cf-4cd4-8056-6fd00f4d9699')
        ->and($block->serviceId)->toBe('bd26d8db-86a4-4f18-9e94-1b2362a1073c')
        ->and($block->assignedResourceId)->toBe('68aa4760-1b63-452e-9060-b32501247b08')
        ->and($block->startUtc)->toBeInstanceOf(Carbon::class)
        ->and($block->startUtc->toIso8601String())->toBe('2025-01-20T14:00:00+00:00')
        ->and($block->endUtc)->toBeInstanceOf(Carbon::class)
        ->and($block->endUtc->toIso8601String())->toBe('2025-01-25T10:00:00+00:00')
        ->and($block->type)->toBe('OutOfOrder')
        ->and($block->reservationId)->toBeNull();
});

it('handles resource block with reservation ID', function () {
    $data = [
        'Id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
        'ServiceId' => 'bd26d8db-86a4-4f18-9e94-1b2362a1073c',
        'AssignedResourceId' => null,
        'StartUtc' => '2025-02-01T14:00:00Z',
        'EndUtc' => '2025-02-05T10:00:00Z',
        'Type' => 'OutOfService',
        'ReservationId' => 'bfee2c44-1f84-4326-a862-5289598a6cea',
    ];

    $block = ResourceBlock::fromApiResponse($data);

    expect($block->id)->toBe('a1b2c3d4-e5f6-7890-abcd-ef1234567890')
        ->and($block->assignedResourceId)->toBeNull()
        ->and($block->type)->toBe('OutOfService')
        ->and($block->reservationId)->toBe('bfee2c44-1f84-4326-a862-5289598a6cea');
});

it('parses resource blocks from mock API response', function () {
    $mockPath = __DIR__ . '/../../../../mocks/mews/responses/resourceblocks-get.json';
    $mockData = json_decode(file_get_contents($mockPath), true);
    $blockData = $mockData['ResourceBlocks'][0];

    $block = ResourceBlock::fromApiResponse($blockData);

    expect($block->id)->toBe('7cccbdc6-73cf-4cd4-8056-6fd00f4d9699')
        ->and($block->serviceId)->toBe('bd26d8db-86a4-4f18-9e94-1b2362a1073c')
        ->and($block->type)->toBe('OutOfOrder');
});

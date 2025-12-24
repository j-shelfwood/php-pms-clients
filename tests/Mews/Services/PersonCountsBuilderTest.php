<?php

use Shelfwood\PhpPms\Mews\MewsConnectorAPI;
use Shelfwood\PhpPms\Mews\Services\PersonCountsBuilder;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\AgeCategory;
use Shelfwood\PhpPms\Mews\Enums\AgeClassification;

it('builds person counts for adults only', function () {
    $client = Mockery::mock(MewsConnectorAPI::class);
    $client->shouldReceive('getAdultAgeCategory')
        ->once()
        ->andReturn(new AgeCategory(
            id: 'adult-id',
            serviceId: 'service-1',
            classification: AgeClassification::Adult,
            minimalAge: 0,
            maximalAge: null,
            names: ['en-GB' => 'Adult'],
            shortNames: null,
            isActive: true,
            createdUtc: '',
            updatedUtc: ''
        ));
    $client->shouldNotReceive('getChildAgeCategory');

    $result = PersonCountsBuilder::fromAdultsChildren($client, 'service-1', adults: 2, children: 0);

    expect($result)->toBe([
        ['AgeCategoryId' => 'adult-id', 'Count' => 2],
    ]);
});

it('builds person counts for adults and children', function () {
    $client = Mockery::mock(MewsConnectorAPI::class);
    $client->shouldReceive('getAdultAgeCategory')
        ->once()
        ->andReturn(new AgeCategory(
            id: 'adult-id',
            serviceId: 'service-1',
            classification: AgeClassification::Adult,
            minimalAge: 0,
            maximalAge: null,
            names: ['en-GB' => 'Adult'],
            shortNames: null,
            isActive: true,
            createdUtc: '',
            updatedUtc: ''
        ));
    $client->shouldReceive('getChildAgeCategory')
        ->once()
        ->andReturn(new AgeCategory(
            id: 'child-id',
            serviceId: 'service-1',
            classification: AgeClassification::Child,
            minimalAge: 0,
            maximalAge: 17,
            names: ['en-GB' => 'Child'],
            shortNames: null,
            isActive: true,
            createdUtc: '',
            updatedUtc: ''
        ));

    $result = PersonCountsBuilder::fromAdultsChildren($client, 'service-1', adults: 2, children: 1);

    expect($result)->toBe([
        ['AgeCategoryId' => 'adult-id', 'Count' => 2],
        ['AgeCategoryId' => 'child-id', 'Count' => 1],
    ]);
});

it('throws when no adults or children provided', function () {
    $client = Mockery::mock(MewsConnectorAPI::class);

    PersonCountsBuilder::fromAdultsChildren($client, 'service-1', adults: 0, children: 0);
})->throws(\InvalidArgumentException::class, 'At least one adult or child must be specified for reservation');

it('throws when adult category missing', function () {
    $client = Mockery::mock(MewsConnectorAPI::class);
    $client->shouldReceive('getAdultAgeCategory')->once()->andReturn(null);

    PersonCountsBuilder::fromAdultsChildren($client, 'service-1', adults: 1, children: 0);
})->throws(\RuntimeException::class, 'No active Adult age category found');

it('throws when child category missing', function () {
    $client = Mockery::mock(MewsConnectorAPI::class);
    $client->shouldReceive('getAdultAgeCategory')
        ->once()
        ->andReturn(new AgeCategory(
            id: 'adult-id',
            serviceId: 'service-1',
            classification: AgeClassification::Adult,
            minimalAge: 0,
            maximalAge: null,
            names: ['en-GB' => 'Adult'],
            shortNames: null,
            isActive: true,
            createdUtc: '',
            updatedUtc: ''
        ));
    $client->shouldReceive('getChildAgeCategory')->once()->andReturn(null);

    PersonCountsBuilder::fromAdultsChildren($client, 'service-1', adults: 1, children: 1);
})->throws(\RuntimeException::class, 'No active Child age category found');


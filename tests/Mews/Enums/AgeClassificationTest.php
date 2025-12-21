<?php

use Shelfwood\PhpPms\Mews\Enums\AgeClassification;

it('has all age classifications', function () {
    expect(AgeClassification::cases())->toHaveCount(3)
        ->and(AgeClassification::Adult->value)->toBe('Adult')
        ->and(AgeClassification::Child->value)->toBe('Child')
        ->and(AgeClassification::Infant->value)->toBe('Infant');
});

it('can be created from string', function () {
    $age = AgeClassification::from('Adult');
    expect($age)->toBe(AgeClassification::Adult);
});

it('throws on invalid value', function () {
    AgeClassification::from('InvalidAge');
})->throws(\ValueError::class);

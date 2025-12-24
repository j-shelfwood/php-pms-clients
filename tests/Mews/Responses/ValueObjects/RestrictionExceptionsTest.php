<?php

use Shelfwood\PhpPms\Mews\Responses\ValueObjects\RestrictionExceptions;

it('maps restriction exceptions with optional fields', function () {
    $exceptions = RestrictionExceptions::map([
        'MinLength' => 'P0M3DT0H0M0S',
        'MaxLength' => null,
    ]);

    expect($exceptions->minLength)->toBe('P0M3DT0H0M0S')
        ->and($exceptions->maxLength)->toBeNull();
});


<?php

use Shelfwood\PhpPms\Mews\Enums\RestrictionType;

it('maps restriction types from strings', function () {
    expect(RestrictionType::from('Start'))->toBe(RestrictionType::Start)
        ->and(RestrictionType::from('Stay'))->toBe(RestrictionType::Stay);
});


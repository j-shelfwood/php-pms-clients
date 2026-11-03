<?php

use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Payloads\CreateReservationPayload;
use Shelfwood\PhpPms\Mews\Payloads\TimeUnitPrice;

function payloadWithPrices(?array $prices, string $start = '2025-01-14T23:00:00Z', string $end = '2025-01-17T23:00:00Z'): CreateReservationPayload
{
    return new CreateReservationPayload(
        serviceId: 'service-123',
        customerId: 'customer-456',
        rateId: 'rate-789',
        startUtc: Carbon::parse($start),
        endUtc: Carbon::parse($end),
        personCounts: [['AgeCategoryId' => 'adult', 'Count' => 2]],
        timeUnitPrices: $prices,
    );
}

it('omits TimeUnitPrices when not given', function () {
    expect(payloadWithPrices(null)->toArray())->not->toHaveKey('TimeUnitPrices')
        ->and(payloadWithPrices(null)->toArray())->not->toHaveKey('TimeUnitAmount');
});

it('serialises gross TimeUnitPrices in index order given', function () {
    $array = payloadWithPrices([
        TimeUnitPrice::gross(0, 120.5, 'EUR', ['NL-2019-L']),
        TimeUnitPrice::gross(1, 130.0, 'EUR', ['NL-2019-L']),
        TimeUnitPrice::gross(2, 0.0, 'EUR', ['NL-2019-L']),
    ])->toArray();

    expect($array['TimeUnitPrices'])->toBe([
        ['Index' => 0, 'Amount' => ['Currency' => 'EUR', 'GrossValue' => 120.5, 'TaxCodes' => ['NL-2019-L']]],
        ['Index' => 1, 'Amount' => ['Currency' => 'EUR', 'GrossValue' => 130.0, 'TaxCodes' => ['NL-2019-L']]],
        ['Index' => 2, 'Amount' => ['Currency' => 'EUR', 'GrossValue' => 0.0, 'TaxCodes' => ['NL-2019-L']]],
    ])->and($array)->not->toHaveKey('TimeUnitAmount');
});

it('serialises net TimeUnitPrices with several tax codes', function () {
    $array = payloadWithPrices([
        TimeUnitPrice::net(1, 100.0, 'EUR', ['NL-2019-L', 'NL-CITY']),
    ])->toArray();

    expect($array['TimeUnitPrices'])->toBe([
        ['Index' => 1, 'Amount' => ['Currency' => 'EUR', 'NetValue' => 100.0, 'TaxCodes' => ['NL-2019-L', 'NL-CITY']]],
    ]);
});

it('counts nights from UTC calendar dates across a DST change', function () {
    // Europe/Amsterdam midnight 2025-03-29 (23:00Z) to 2025-03-31 (22:00Z): 2 nights
    $payload = payloadWithPrices([
        TimeUnitPrice::gross(0, 100, 'EUR', ['T']),
        TimeUnitPrice::gross(1, 100, 'EUR', ['T']),
    ], '2025-03-28T23:00:00Z', '2025-03-30T22:00:00Z');

    expect($payload->toArray()['TimeUnitPrices'])->toHaveCount(2);

    expect(fn () => payloadWithPrices([TimeUnitPrice::gross(2, 100, 'EUR', ['T'])], '2025-03-28T23:00:00Z', '2025-03-30T22:00:00Z'))
        ->toThrow(InvalidArgumentException::class, 'Index 2 out of range 0..1');
});

it('rejects an index at or beyond the night count', function () {
    payloadWithPrices([TimeUnitPrice::gross(3, 100, 'EUR', ['T'])]);
})->throws(InvalidArgumentException::class, 'TimeUnitPrice Index 3 out of range 0..2');

it('rejects a negative index', function () {
    TimeUnitPrice::gross(-1, 100, 'EUR', ['T']);
})->throws(InvalidArgumentException::class, 'Index must be >= 0');

it('rejects duplicate indexes', function () {
    payloadWithPrices([
        TimeUnitPrice::gross(1, 100, 'EUR', ['T']),
        TimeUnitPrice::gross(1, 110, 'EUR', ['T']),
    ]);
})->throws(InvalidArgumentException::class, 'Duplicate TimeUnitPrice Index 1');

it('rejects an empty TimeUnitPrices array', function () {
    payloadWithPrices([]);
})->throws(InvalidArgumentException::class, 'TimeUnitPrices cannot be an empty array');

it('rejects non TimeUnitPrice elements', function () {
    payloadWithPrices([['Index' => 0, 'Amount' => ['Currency' => 'EUR', 'GrossValue' => 1, 'TaxCodes' => ['T']]]]);
})->throws(InvalidArgumentException::class, 'must contain only TimeUnitPrice instances');

it('rejects both gross and net', function () {
    new TimeUnitPrice(index: 0, currency: 'EUR', taxCodes: ['T'], grossValue: 100, netValue: 90);
})->throws(InvalidArgumentException::class, 'exactly one of GrossValue or NetValue');

it('rejects neither gross nor net', function () {
    new TimeUnitPrice(index: 0, currency: 'EUR', taxCodes: ['T']);
})->throws(InvalidArgumentException::class, 'exactly one of GrossValue or NetValue');

it('rejects empty TaxCodes', function () {
    TimeUnitPrice::net(0, 100, 'EUR', []);
})->throws(InvalidArgumentException::class, 'TaxCodes cannot be empty');

it('rejects a blank tax code', function () {
    TimeUnitPrice::net(0, 100, 'EUR', ['']);
})->throws(InvalidArgumentException::class, 'TaxCodes must be non-empty strings');

it('rejects more than one tax code with gross', function () {
    TimeUnitPrice::gross(0, 100, 'EUR', ['A', 'B']);
})->throws(InvalidArgumentException::class, 'only one TaxCode with GrossValue');

it('rejects a negative gross amount', function () {
    TimeUnitPrice::gross(0, -0.01, 'EUR', ['T']);
})->throws(InvalidArgumentException::class, 'amount must be >= 0');

it('rejects a negative net amount', function () {
    TimeUnitPrice::net(0, -5, 'EUR', ['T']);
})->throws(InvalidArgumentException::class, 'amount must be >= 0');

it('rejects an empty currency', function () {
    TimeUnitPrice::gross(0, 100, ' ', ['T']);
})->throws(InvalidArgumentException::class, 'Currency cannot be empty');

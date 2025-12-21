<?php

namespace Shelfwood\PhpPms\Tests\Services;

use Illuminate\Support\Carbon;
use Shelfwood\PhpPms\Mews\Services\DateConverter;

describe('DateConverter', function () {
    describe('toEnterpriseMidnightUtc', function () {
        it('converts UTC midnight to Budapest midnight (winter)', function () {
            $utcDate = Carbon::parse('2025-01-15 00:00:00 UTC');
            $result = DateConverter::toEnterpriseMidnightUtc($utcDate, 'Europe/Budapest');

            expect($result->toIso8601String())->toBe('2025-01-14T23:00:00+00:00')
                ->and($result->timezone->getName())->toBe('UTC');
        });

        it('converts UTC midnight to Budapest midnight (summer DST)', function () {
            $utcDate = Carbon::parse('2025-07-15 00:00:00 UTC');
            $result = DateConverter::toEnterpriseMidnightUtc($utcDate, 'Europe/Budapest');

            // Budapest is UTC+2 during summer DST
            expect($result->toIso8601String())->toBe('2025-07-14T22:00:00+00:00')
                ->and($result->timezone->getName())->toBe('UTC');
        });

        it('handles month boundaries correctly', function () {
            $utcDate = Carbon::parse('2025-12-01 00:00:00 UTC');
            $result = DateConverter::toEnterpriseMidnightUtc($utcDate, 'Europe/Budapest');

            expect($result->toIso8601String())->toBe('2025-11-30T23:00:00+00:00');
        });

        it('handles year boundaries correctly', function () {
            $utcDate = Carbon::parse('2026-01-01 00:00:00 UTC');
            $result = DateConverter::toEnterpriseMidnightUtc($utcDate, 'Europe/Budapest');

            expect($result->toIso8601String())->toBe('2025-12-31T23:00:00+00:00');
        });

        it('does not mutate original date object', function () {
            $utcDate = Carbon::parse('2025-01-15 00:00:00 UTC');
            $original = $utcDate->toIso8601String();

            DateConverter::toEnterpriseMidnightUtc($utcDate, 'Europe/Budapest');

            expect($utcDate->toIso8601String())->toBe($original);
        });

        it('works with different timezones', function () {
            $utcDate = Carbon::parse('2025-01-15 00:00:00 UTC');

            // America/New_York is UTC-5 in winter
            // Jan 15 00:00 UTC → Jan 14 19:00 NY → Jan 14 00:00 NY → Jan 14 05:00 UTC
            $result = DateConverter::toEnterpriseMidnightUtc($utcDate, 'America/New_York');
            expect($result->toIso8601String())->toBe('2025-01-14T05:00:00+00:00');

            // Asia/Tokyo is UTC+9 year-round
            // Jan 15 00:00 UTC → Jan 15 09:00 Tokyo → Jan 15 00:00 Tokyo → Jan 14 15:00 UTC
            $result = DateConverter::toEnterpriseMidnightUtc($utcDate, 'Asia/Tokyo');
            expect($result->toIso8601String())->toBe('2025-01-14T15:00:00+00:00');
        });

        it('handles DST transition dates correctly', function () {
            // Europe/Budapest transitions to DST on last Sunday of March at 2:00 AM
            // In 2025, that's March 30
            $beforeDst = Carbon::parse('2025-03-29 00:00:00 UTC');
            $afterDst = Carbon::parse('2025-03-31 00:00:00 UTC');

            $resultBefore = DateConverter::toEnterpriseMidnightUtc($beforeDst, 'Europe/Budapest');
            $resultAfter = DateConverter::toEnterpriseMidnightUtc($afterDst, 'Europe/Budapest');

            // Before DST: UTC+1 (23:00 previous day)
            expect($resultBefore->toIso8601String())->toBe('2025-03-28T23:00:00+00:00');

            // After DST: UTC+2 (22:00 previous day)
            expect($resultAfter->toIso8601String())->toBe('2025-03-30T22:00:00+00:00');
        });
    });

    describe('convertDateRange', function () {
        it('converts both start and end dates', function () {
            $start = Carbon::parse('2025-01-01 00:00:00 UTC');
            $end = Carbon::parse('2025-01-31 00:00:00 UTC');

            $result = DateConverter::convertDateRange($start, $end, 'Europe/Budapest');

            expect($result)->toHaveKey('start')
                ->and($result)->toHaveKey('end')
                ->and($result['start']->toIso8601String())->toBe('2024-12-31T23:00:00+00:00')
                ->and($result['end']->toIso8601String())->toBe('2025-01-30T23:00:00+00:00');
        });

        it('returns Carbon instances in UTC timezone', function () {
            $start = Carbon::parse('2025-01-01 00:00:00 UTC');
            $end = Carbon::parse('2025-01-31 00:00:00 UTC');

            $result = DateConverter::convertDateRange($start, $end, 'Europe/Budapest');

            expect($result['start']->timezone->getName())->toBe('UTC')
                ->and($result['end']->timezone->getName())->toBe('UTC');
        });

        it('does not mutate original date objects', function () {
            $start = Carbon::parse('2025-01-01 00:00:00 UTC');
            $end = Carbon::parse('2025-01-31 00:00:00 UTC');

            $originalStart = $start->toIso8601String();
            $originalEnd = $end->toIso8601String();

            DateConverter::convertDateRange($start, $end, 'Europe/Budapest');

            expect($start->toIso8601String())->toBe($originalStart)
                ->and($end->toIso8601String())->toBe($originalEnd);
        });
    });

    describe('Mews API compliance', function () {
        it('produces dates compliant with Mews API requirements', function () {
            // Mews API requires: "00:00 converted to UTC"
            // Meaning: midnight in enterprise timezone, expressed as UTC

            $utcDate = Carbon::parse('2025-11-01 00:00:00 UTC');
            $result = DateConverter::toEnterpriseMidnightUtc($utcDate, 'Europe/Budapest');

            // November is winter (UTC+1), so midnight Nov 1 Budapest = 23:00 Oct 31 UTC
            expect($result->toIso8601String())->toBe('2025-10-31T23:00:00+00:00');

            // Verify it's actually midnight in Budapest
            $budapestTime = $result->copy()->setTimezone('Europe/Budapest');
            expect($budapestTime->format('H:i:s'))->toBe('00:00:00');
        });

        it('handles time unit start boundaries as Mews expects', function () {
            // Mews: "FirstTimeUnitStartUtc must correspond to start boundary of time unit"
            // For "Day" time units, that's 00:00 in enterprise timezone

            $startOfMonth = Carbon::parse('2025-12-01 00:00:00 UTC');
            $endOfMonth = Carbon::parse('2025-12-31 00:00:00 UTC');

            $converted = DateConverter::convertDateRange($startOfMonth, $endOfMonth, 'Europe/Budapest');

            // Verify both are midnight in Budapest
            $startBudapest = $converted['start']->copy()->setTimezone('Europe/Budapest');
            $endBudapest = $converted['end']->copy()->setTimezone('Europe/Budapest');

            expect($startBudapest->format('H:i:s'))->toBe('00:00:00')
                ->and($endBudapest->format('H:i:s'))->toBe('00:00:00');
        });
    });
});

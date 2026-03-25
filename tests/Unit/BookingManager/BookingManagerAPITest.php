<?php

declare(strict_types=1);

namespace Tests\Unit\BookingManager;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Psr\Log\NullLogger;
use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\CalendarResponse;
use Shelfwood\PhpPms\Exceptions\ApiException;

function createApiWithXmlResponse(string $xmlBody): BookingManagerAPI
{
    $mock = new MockHandler([
        new Response(200, ['Content-Type' => 'text/xml'], $xmlBody),
    ]);
    $client = new Client(['handler' => HandlerStack::create($mock)]);

    return new BookingManagerAPI(
        httpClient: $client,
        apiKey: 'test-key',
        baseUrl: 'https://example.com/api',
        logger: new NullLogger(),
    );
}

describe('BookingManagerAPI::calendar', function () {
    test('returns empty CalendarResponse when API returns "No results" error code 300', function () {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><response><error><code>300</code><message>No results</message></error></response>';
        $api = createApiWithXmlResponse($xml);

        $response = $api->calendar(12345, Carbon::parse('2026-01-01'), Carbon::parse('2026-12-31'));

        expect($response)->toBeInstanceOf(CalendarResponse::class)
            ->and($response->propertyId)->toBe(12345)
            ->and($response->days)->toBeEmpty();
    });

    test('handles "No results" with attribute-style error XML', function () {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><error code="300">No results</error>';
        $api = createApiWithXmlResponse($xml);

        $response = $api->calendar(99, Carbon::parse('2026-01-01'), Carbon::parse('2026-03-31'));

        expect($response)->toBeInstanceOf(CalendarResponse::class)
            ->and($response->propertyId)->toBe(99)
            ->and($response->days)->toBeEmpty();
    });

    test('throws ApiException for non-300 error codes', function () {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><response><error><code>500</code><message>Internal error</message></error></response>';
        $api = createApiWithXmlResponse($xml);

        $api->calendar(12345, Carbon::parse('2026-01-01'), Carbon::parse('2026-12-31'));
    })->throws(ApiException::class, 'Internal error');

    test('throws ApiException for code 300 with different message', function () {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><response><error><code>300</code><message>Some other error</message></error></response>';
        $api = createApiWithXmlResponse($xml);

        $api->calendar(12345, Carbon::parse('2026-01-01'), Carbon::parse('2026-12-31'));
    })->throws(ApiException::class, 'Some other error');
});

describe('BookingManagerAPI::availability', function () {
    test('returns empty CalendarResponse when API returns "No results" error code 300', function () {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><response><error><code>300</code><message>No results</message></error></response>';
        $api = createApiWithXmlResponse($xml);

        $response = $api->availability(12345, Carbon::parse('2026-01-01'), Carbon::parse('2026-12-31'));

        expect($response)->toBeInstanceOf(CalendarResponse::class)
            ->and($response->propertyId)->toBe(12345)
            ->and($response->days)->toBeEmpty();
    });
});

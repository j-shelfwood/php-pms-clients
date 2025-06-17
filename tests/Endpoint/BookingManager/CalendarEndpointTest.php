<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Enums\SeasonType; // Added import
use Shelfwood\PhpPms\BookingManager\Responses\CalendarResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarDayInfo;
use Psr\Log\NullLogger;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Carbon\Carbon;
use Tests\Helpers\TestHelpers;


describe('CalendarEndpointTest', function () {
    beforeEach(function () {
        $this->mockHttpClient = $this->createMock(ClientInterface::class);
        $this->api = new BookingManagerAPI(
            $this->mockHttpClient,
            'dummy-api-key',
            'https://dummy-url',
            new NullLogger()
        );
    });

        test('BookingManagerAPI::calendar uses info.xml and correctly maps the response with rates', function () {
        // Create a mock XML response for info.xml with available property and rates
        $mockXml = '<?xml version="1.0" encoding="UTF-8"?>
<response>
    <rate>
        <info arrival="2024-02-19" departure="2024-02-20" nights="1">
            <property id="21663" identifier="#487" max_persons="2" available="1" minimal_nights="1">
                <rate currency="EUR">
                    <total>220.00</total>
                    <final>220.00</final>
                    <tax total="35.20">
                        <vat value="9">19.80</vat>
                        <other type="relative" value="7">15.40</other>
                        <final>255.20</final>
                    </tax>
                    <fee>0.00</fee>
                    <prepayment>66.00</prepayment>
                    <balance_due>189.20</balance_due>
                </rate>
            </property>
        </info>
    </rate>
</response>';

        $mockHttpResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($mockXml);
        $mockHttpResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockHttpResponse);

        $startDate = Carbon::parse('2024-02-19');
        $endDate = Carbon::parse('2024-02-20');

        // Call the calendar method
        $response = $this->api->calendar(21663, $startDate, $endDate);

        // Assertions
        expect($response)->toBeInstanceOf(CalendarResponse::class);
        expect($response->days)->toBeArray();
        expect($response->days)->not()->toBeEmpty();

        // Check that we have calendar data for the requested dates (2 days)
        expect($response->days)->toHaveCount(2);

        $firstDay = $response->days[0];
        expect($firstDay)->toBeInstanceOf(CalendarDayInfo::class);
        expect($firstDay->available)->toBe(1); // Available
        expect($firstDay->day->toDateString())->toBe('2024-02-19');
        expect($firstDay->rate->final)->toBe(220.0); // Check that rates are populated

        $secondDay = $response->days[1];
        expect($secondDay->day->toDateString())->toBe('2024-02-20');
        expect($secondDay->available)->toBe(1); // Available
        expect($secondDay->rate->final)->toBe(220.0); // Check that rates are populated
    });
    // Removed test for HttpClientException (no longer used in new exception hierarchy)

});

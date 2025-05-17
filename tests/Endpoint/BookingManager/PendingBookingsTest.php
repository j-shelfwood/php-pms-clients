
<?php

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use GuzzleHttp\ClientInterface;
use Psr\Log\NullLogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Tests\Helpers\TestHelpers;

describe('PendingBookingsTest', function () {
    beforeEach(function () {
        $this->mockHttpClient = $this->createMock(ClientInterface::class);
        $this->api = new BookingManagerAPI(
            $this->mockHttpClient,
            'dummy-api-key',
            'dummy-username',
            'https://dummy-url',
            new NullLogger()
        );
    });

    test('returns pending bookings for a given booking id', function () {
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/pending-bookings.xml');
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->pendingBookings(16);
        expect($response)->not()->toBeNull();
        expect($response->pendingBookings)->toBeArray();
        expect($response->pendingBookings[0]->bookingId)->toBe(16);
    });
});

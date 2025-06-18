<?php


use Shelfwood\PhpPms\BookingManager\Payloads\EditBookingPayload;
use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use GuzzleHttp\ClientInterface;
use Psr\Log\NullLogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Tests\Helpers\TestHelpers;

describe('EditBookingTest', function () {
    beforeEach(function () {
        $this->mockHttpClient = $this->createMock(ClientInterface::class);
        $this->api = new BookingManagerAPI(
            $this->mockHttpClient,
            'dummy-api-key',
            'https://dummy-url',
            new NullLogger()
        );
    });

    test('edits a booking and returns updated booking data', function () {
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/edit-booking.xml');
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $payload = new EditBookingPayload(
            id: 16,
            amount_childs: 1
        );
        $response = $this->api->editBooking($payload);
        expect($response)->not()->toBeNull();
        expect($response->booking->id)->toBe(16);
        expect($response->booking->amount_children)->toBe(1);
    });
});

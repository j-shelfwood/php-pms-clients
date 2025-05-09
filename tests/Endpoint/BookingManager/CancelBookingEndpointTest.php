<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\CancelBookingResponse;
use Psr\Log\NullLogger;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

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

test('BookingManagerAPI::cancelBooking returns CancelBookingResponse success false on failure', function () {
    $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/cancel-booking.xml');
    $mockResponse = $this->createMock(ResponseInterface::class);
    $mockStream = $this->createMock(StreamInterface::class);
    $mockStream->method('getContents')->willReturn($xml);
    $mockResponse->method('getBody')->willReturn($mockStream);
    $this->mockHttpClient->method('request')->willReturn($mockResponse);

    $response = $this->api->cancelBooking(171838, 'reason');

    expect($response)->toBeInstanceOf(CancelBookingResponse::class);
    expect($response->success)->toBeFalse();
    expect($response->message)->toContain('Other:');
});

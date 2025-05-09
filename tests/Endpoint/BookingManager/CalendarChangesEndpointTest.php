<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\CalendarChangesResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\CalendarChange;
use Psr\Log\NullLogger;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Carbon\Carbon;

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

test('BookingManagerAPI::calendarChanges returns CalendarChangesResponse with changes', function () {
    $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/calendar-changes.xml');
    $mockResponse = $this->createMock(ResponseInterface::class);
    $mockStream = $this->createMock(StreamInterface::class);
    $mockStream->method('getContents')->willReturn($xml);
    $mockResponse->method('getBody')->willReturn($mockStream);
    $this->mockHttpClient->method('request')->willReturn($mockResponse);

    $since = Carbon::parse('2023-11-12 00:00:00');
    $response = $this->api->calendarChanges($since);

    expect($response)->toBeInstanceOf(CalendarChangesResponse::class);
    expect($response->amount)->toBe(2);
    expect($response->changes)->toBeArray()->and->not->toBeEmpty();
    $first = $response->changes[0];
    expect($first)->toBeInstanceOf(CalendarChange::class);
    expect($first->propertyId)->toBe(22958);
    expect($first->months)->toContain('2024-02');
});

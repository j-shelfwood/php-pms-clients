<?php

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\RateResponse;
use Tests\Helpers\TestHelpers;

test('it can fetch rate for a stay', function () {
    $mockResponsePath = TestHelpers::getMockFilePath('get-rate-for-stay.xml');
    $mockXmlResponse = file_get_contents($mockResponsePath);

    expect($mockXmlResponse)->not->toBeEmpty('Mock XML file is empty or not loaded: ' . $mockResponsePath);

    $mockHandler = new MockHandler([
        new Response(200, ['Content-Type' => 'application/xml'], $mockXmlResponse),
    ]);
    $httpClient = new Client(['handler' => $mockHandler]);

    $apiKey = 'test_api_key';
    $username = 'test_username';
    $baseUrl = 'https://api.example.com';

    $api = new BookingManagerAPI($httpClient, $apiKey, $username, $baseUrl);

    $propertyId = 21663;
    $arrivalDate = Carbon::parse('2024-02-19');
    $departureDate = Carbon::parse('2024-02-20');
    $numAdults = 2;
    $numChildren = 1;

    $response = $api->rateForStay($propertyId, $arrivalDate, $departureDate, $numAdults, $numChildren);

    expect($response)->toBeInstanceOf(RateResponse::class);
    expect($response->propertyId)->toBe(21663);
    expect($response->final_after_taxes)->toBe(255.20);
    expect($response->available)->toBeFalse();
    expect($response->minimalNights)->toBe(1);
    expect($response->final_before_taxes)->toBe(220.00);
});

test('it can fetch rate for a stay without children or babies', function () {
    $mockResponsePath = TestHelpers::getMockFilePath('get-rate-for-stay.xml');
    $mockXmlResponse = file_get_contents($mockResponsePath);

    expect($mockXmlResponse)->not->toBeEmpty('Mock XML file is empty or not loaded: ' . $mockResponsePath);

    $mockHandler = new MockHandler([
        new Response(200, ['Content-Type' => 'application/xml'], $mockXmlResponse),
    ]);
    $httpClient = new Client(['handler' => $mockHandler]);

    $apiKey = 'test_api_key';
    $username = 'test_username';
    $baseUrl = 'https://api.example.com';

    $api = new BookingManagerAPI($httpClient, $apiKey, $username, $baseUrl);

    $propertyId = 21663;
    $arrivalDate = Carbon::parse('2024-02-19');
    $departureDate = Carbon::parse('2024-02-20');
    $numAdults = 2;

    $response = $api->rateForStay($propertyId, $arrivalDate, $departureDate, $numAdults);

    expect($response)->toBeInstanceOf(RateResponse::class);
    expect($response->propertyId)->toBe(21663);
    expect($response->final_after_taxes)->toBe(255.20);
    expect($response->available)->toBeFalse();
    expect($response->minimalNights)->toBe(1);
    expect($response->final_before_taxes)->toBe(220.00);
});

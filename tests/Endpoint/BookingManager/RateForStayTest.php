<?php

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\RateResponse;
use Tests\Helpers\TestHelpers; // Corrected import

test('it can fetch rate for a stay', function () {
    // Use the helper to get the mock file path
    $mockResponsePath = TestHelpers::getMockFilePath('get-rate-for-stay.xml'); // Corrected filename
    $mockXmlResponse = file_get_contents($mockResponsePath);

    expect($mockXmlResponse)->not->toBeEmpty('Mock XML file is empty or not loaded: ' . $mockResponsePath);

    $mockHandler = new MockHandler([
        new Response(200, ['Content-Type' => 'application/xml'], $mockXmlResponse),
    ]);
    $httpClient = new Client(['handler' => $mockHandler]);

    $apiKey = 'test_api_key';
    $username = 'test_username';
    $baseUrl = 'https://api.example.com'; // Example base URL

    $api = new BookingManagerAPI($httpClient, $apiKey, $username, $baseUrl);

    $propertyId = 21663; // Corrected property ID to match mock
    $arrivalDate = Carbon::parse('2024-02-19');
    $departureDate = Carbon::parse('2024-02-20');
    $numAdults = 2;
    $numChildren = 1; // Assuming this test case includes a child

    $response = $api->rateForStay($propertyId, $arrivalDate, $departureDate, $numAdults, $numChildren);

    expect($response)->toBeInstanceOf(RateResponse::class);
    expect($response->propertyId)->toBe(21663); // Assert against mock's ID
    // Values from get-rate-for-stay.xml:
    // <property id="21663" ... available="0" minimal_nights="1">
    // <rate currency="EUR">
    //   <total>220.00</total>
    //   <final>220.00</final>
    //   <tax total="35.20">
    //     <final>255.20</final>
    //   </tax>
    expect($response->final_after_taxes)->toBe(255.20); // Corrected to match mock's tax->final
    expect($response->available)->toBeFalse(); // Corrected, mock says available="0"
    expect($response->minimalNights)->toBe(1);
    // expect($response->currency)->toBe('EUR'); // Currency is not a direct public property of RateResponse
    expect($response->final_before_taxes)->toBe(220.00); // Matches mock's rate->final (which maps to final_before_taxes)
});

test('it can fetch rate for a stay without children or babies', function () {
    // Use the helper to get the mock file path
    $mockResponsePath = TestHelpers::getMockFilePath('get-rate-for-stay.xml'); // Corrected filename
    $mockXmlResponse = file_get_contents($mockResponsePath); // Adjust mock if needed for this specific case

    expect($mockXmlResponse)->not->toBeEmpty('Mock XML file is empty or not loaded: ' . $mockResponsePath);

    $mockHandler = new MockHandler([
        new Response(200, ['Content-Type' => 'application/xml'], $mockXmlResponse),
    ]);
    $httpClient = new Client(['handler' => $mockHandler]);

    // Provide all required arguments for BookingManagerAPI constructor
    $apiKey = 'test_api_key';
    $username = 'test_username';
    $baseUrl = 'https://api.example.com'; // Example base URL

    $api = new BookingManagerAPI($httpClient, $apiKey, $username, $baseUrl);

    $propertyId = 21663; // Corrected property ID to match mock
    $arrivalDate = Carbon::parse('2024-02-19');
    $departureDate = Carbon::parse('2024-02-20');
    $numAdults = 2;

    $response = $api->rateForStay($propertyId, $arrivalDate, $departureDate, $numAdults);

    expect($response)->toBeInstanceOf(RateResponse::class);
    expect($response->propertyId)->toBe(21663); // Assert against mock's ID
    expect($response->final_after_taxes)->toBe(255.20); // Corrected to match mock's tax->final
    expect($response->available)->toBeFalse(); // Corrected, mock says available="0"
    expect($response->minimalNights)->toBe(1);
    // expect($response->currency)->toBe('EUR'); // Currency is not a direct public property of RateResponse
    expect($response->final_before_taxes)->toBe(220.00); // Matches mock's rate->final (which maps to final_before_taxes)
});

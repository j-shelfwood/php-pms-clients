<?php

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\PropertyResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyDetails;
use Shelfwood\PhpPms\BookingManager\Enums\PropertyStatus;
use Shelfwood\PhpPms\BookingManager\Enums\ViewType;
use Shelfwood\PhpPms\BookingManager\Enums\InternetType;
use Shelfwood\PhpPms\BookingManager\Enums\InternetConnectionType;
use Shelfwood\PhpPms\BookingManager\Enums\ParkingType;
use Shelfwood\PhpPms\BookingManager\Enums\SwimmingPoolType;
use Shelfwood\PhpPms\BookingManager\Enums\SaunaType;
use Shelfwood\PhpPms\BookingManager\Enums\TaxType;
use Shelfwood\PhpPms\BookingManager\Enums\TvType;
use Shelfwood\PhpPms\BookingManager\Enums\TvConnectionType;
use Shelfwood\PhpPms\BookingManager\Enums\DvdType;
use Tests\Helpers\TestHelpers;

test('it can fetch a single property', function () {
    $mockResponsePath = TestHelpers::getMockFilePath('property-by-id.xml');
    $mockXmlResponse = file_get_contents($mockResponsePath);

    expect($mockXmlResponse)->not->toBeEmpty('Mock XML file is empty or not loaded.');

    $mockHandler = new MockHandler([
        new Response(200, ['Content-Type' => 'application/xml'], $mockXmlResponse),
    ]);
    $httpClient = new Client(['handler' => $mockHandler]);

    $apiKey = 'test_api_key';
    $username = 'test_username';
    $baseUrl = 'https://api.example.com';

    $api = new BookingManagerAPI($httpClient, $apiKey, $username, $baseUrl);

    $propertyId = 21663;
    $response = $api->property($propertyId);

    expect($response)->toBeInstanceOf(PropertyResponse::class);
    expect($response->property)->toBeInstanceOf(PropertyDetails::class);
    expect($response->property->external_id)->toBe($propertyId);
    expect($response->property->name)->toBe('Runstraat suite Amsterdam');
    expect($response->property->max_persons)->toBe(2);
    expect($response->property->status)->toBeInstanceOf(PropertyStatus::class);
    expect($response->property->status)->toBe(PropertyStatus::LIVE);
    expect($response->property->view)->toBeInstanceOf(ViewType::class);
    expect($response->property->view)->toBe(ViewType::STREET);
    expect($response->property->internet)->toBeInstanceOf(InternetType::class);
    expect($response->property->internet)->toBe(InternetType::WIFI);
    expect($response->property->internet_connection)->toBeInstanceOf(InternetConnectionType::class);
    expect($response->property->internet_connection)->toBe(InternetConnectionType::HIGHSPEED);
    expect($response->property->parking)->toBeInstanceOf(ParkingType::class);
    expect($response->property->parking)->toBe(ParkingType::NONE);
    expect($response->property->swimmingpool)->toBeInstanceOf(SwimmingPoolType::class);
    expect($response->property->swimmingpool)->toBe(SwimmingPoolType::NONE);
    expect($response->property->sauna)->toBeInstanceOf(SaunaType::class);
    expect($response->property->sauna)->toBe(SaunaType::NONE);
    expect($response->property->tv)->toBeInstanceOf(TvType::class);
    expect($response->property->tv)->toBe(TvType::FLATSCREEN);
    expect($response->property->tv_connection)->toBeInstanceOf(TvConnectionType::class);
    expect($response->property->tv_connection)->toBe(TvConnectionType::CABLE);
    expect($response->property->dvd)->toBeInstanceOf(DvdType::class);
    expect($response->property->dvd)->toBe(DvdType::NONE);
    expect($response->property->tax)->toBeInstanceOf(\Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyTax::class);
    expect($response->property->tax->otherType)->toBeInstanceOf(TaxType::class);
    expect($response->property->tax->otherType)->toBe(TaxType::RELATIVE);
    expect($response->property->external_created_at)->toBeInstanceOf(Carbon::class);
    expect($response->property->external_created_at->toIso8601String())->toBe('2014-01-15T15:14:54+00:00');
    expect($response->property->external_updated_at)->toBeInstanceOf(Carbon::class);
    expect($response->property->external_updated_at->toIso8601String())->toBe('2023-11-10T08:55:55+00:00');
});

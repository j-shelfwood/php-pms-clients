<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\PropertiesResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyDetails;
use Tests\Helpers\TestHelpers;
use Shelfwood\PhpPms\BookingManager\Enums\PropertyStatus;
use Shelfwood\PhpPms\BookingManager\Enums\ViewType;
use Shelfwood\PhpPms\BookingManager\Enums\InternetType;
use Shelfwood\PhpPms\BookingManager\Enums\InternetConnectionType;
use Shelfwood\PhpPms\BookingManager\Enums\ParkingType;
use Shelfwood\PhpPms\BookingManager\Enums\SwimmingPoolType;
use Shelfwood\PhpPms\BookingManager\Enums\SaunaType;
use Shelfwood\PhpPms\BookingManager\Enums\TaxType;

test('it can fetch all properties', function () {

    $mockXmlPath = TestHelpers::getMockFilePath('all-properties.xml');
    $mockXmlResponse = file_get_contents($mockXmlPath);
    if ($mockXmlResponse === false) {
        throw new \Exception("Failed to load mock XML file: {$mockXmlPath}");
    }
    if (empty(trim($mockXmlResponse))) {
        throw new \Exception("Mock XML file is empty: {$mockXmlPath}");
    }

    $mockHandler = new MockHandler([
        new Response(200, ['Content-Type' => 'application/xml'], $mockXmlResponse),
    ]);
    $httpClient = new Client(['handler' => $mockHandler]);

    $apiKey = 'test_api_key';
    $username = 'test_username';
    $baseUrl = 'https://api.example.com';

    $api = new BookingManagerAPI($httpClient, $apiKey, $username, $baseUrl);

    $response = $api->properties();

    expect($response)->toBeInstanceOf(PropertiesResponse::class);
    expect($response->properties)->toBeArray();
    expect(count($response->properties))->toBeGreaterThan(2);

    // Property 1 (ID 6743 from mock)
    $property1 = $response->properties[0];
    expect($property1)->toBeInstanceOf(PropertyDetails::class);
    expect($property1->external_id)->toBe(6743);
    expect($property1->name)->toBe('Historical Heart apartment Amsterdam');
    expect($property1->status)->toBeInstanceOf(PropertyStatus::class);
    expect($property1->status->value)->toBe('live');
    expect($property1->view)->toBeNull();
    expect($property1->internet)->toBeNull();
    expect($property1->internet_connection)->toBeNull();
    expect($property1->parking)->toBeNull();
    expect($property1->swimmingpool)->toBeNull();
    expect($property1->sauna)->toBeNull();
    expect($property1->tax)->toBeInstanceOf(\Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyTax::class);
    expect($property1->tax->otherType)->toBeNull();

    // Property 2 (ID 6786 from mock)
    $property2 = $response->properties[1];
    expect($property2)->toBeInstanceOf(PropertyDetails::class);
    expect($property2->external_id)->toBe(6786);
    expect($property2->name)->toBe('Jordan Delight apartment Amsterdam');
    expect($property2->status)->toBeInstanceOf(PropertyStatus::class);
    expect($property2->status->value)->toBe('live');
    expect($property2->view)->toBeNull();
    expect($property2->internet)->toBeNull();
    expect($property2->internet_connection)->toBeNull();
    expect($property2->parking)->toBeNull();
    expect($property2->swimmingpool)->toBeNull();
    expect($property2->sauna)->toBeNull();
    expect($property2->tax)->toBeInstanceOf(\Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyTax::class);
    expect($property2->tax->otherType)->toBeNull();

    // Property 3 (ID 6794 from mock - has more details)
    $property3 = $response->properties[2];
    expect($property3)->toBeInstanceOf(PropertyDetails::class);
    expect($property3->external_id)->toBe(6794);
    expect($property3->name)->toBe('Canal Holiday apartment Amsterdam');
    expect($property3->status)->toBeInstanceOf(PropertyStatus::class);
    expect($property3->status->value)->toBe('live');
    expect($property3->view)->toBeInstanceOf(ViewType::class);
    expect($property3->view->value)->toBe('water');
    expect($property3->internet)->toBeInstanceOf(InternetType::class);
    expect($property3->internet->value)->toBe('wifi');
    expect($property3->internet_connection)->toBeInstanceOf(InternetConnectionType::class);
    expect($property3->internet_connection->value)->toBe('highspeed');
    expect($property3->parking)->toBeInstanceOf(ParkingType::class);
    expect($property3->parking->value)->toBe('public');
    expect($property3->swimmingpool)->toBeInstanceOf(SwimmingPoolType::class);
    expect($property3->swimmingpool->value)->toBe('none');
    expect($property3->sauna)->toBeInstanceOf(SaunaType::class);
    expect($property3->sauna->value)->toBe('none');
    expect($property3->tax)->toBeInstanceOf(\Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyTax::class);
    expect($property3->tax->otherType)->toBeInstanceOf(TaxType::class);
    expect($property3->tax->otherType->value)->toBe('relative');
});

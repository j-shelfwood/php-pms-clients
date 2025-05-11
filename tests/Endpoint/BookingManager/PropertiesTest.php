<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\PropertiesResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyDetails;
use Tests\Helpers\TestHelpers;

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
    expect(count($response->properties))->toBeGreaterThan(0);
    expect($response->properties[0])->toBeInstanceOf(PropertyDetails::class);
    expect($response->properties[0]->external_id)->toBe(6743); // Corrected ID
    expect($response->properties[0]->name)->toBe('Historical Heart apartment Amsterdam'); // Corrected name
    expect($response->properties[1]->external_id)->toBe(6786); // Corrected ID for second property
    expect($response->properties[1]->name)->toBe('Jordan Delight apartment Amsterdam'); // Corrected name for second property
});

<?php

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\PropertyResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyDetails;

test('it can fetch a single property', function () {
    $mockXmlResponse = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<response>
    <property id="123" name="Test Property" status="active">
        <type>Apartment,Villa</type>
        <provider>
            <id>prov1</id>
            <name>Provider Name</name>
        </provider>
        <location>
            <country_code>NL</country_code>
            <zip>1000AA</zip>
            <city>Amsterdam</city>
            <street>Main Street 1</street>
            <latitude>52.370216</latitude>
            <longitude>4.895168</longitude>
        </location>
        <max_persons>2</max_persons>
        <minimal_nights>1</minimal_nights>
        <maximal_nights>30</maximal_nights>
        <floor>1</floor>
        <stairs>false</stairs>
        <size>25.5</size>
        <bedrooms>1</bedrooms>
        <single_bed>0</single_bed>
        <double_bed>1</double_bed>
        <single_sofa>0</single_sofa>
        <double_sofa>0</double_sofa>
        <single_bunk>0</single_bunk>
        <bathrooms>1</bathrooms>
        <toilets>1</toilets>
        <elevator>false</elevator>
        <heating>true</heating>
        <images>
            <image id="img1" group_id="group1" created_at="2023-01-01T09:00:00Z" updated_at="2023-01-01T09:00:00Z">
                <name>Image 1</name>
                <url_original>http://example.com/image1.jpg</url_original>
                <url_large>http://example.com/image1_large.jpg</url_large>
                <url_medium>http://example.com/image1_medium.jpg</url_medium>
                <url_small>http://example.com/image1_small.jpg</url_small>
                <url_thumb>http://example.com/image1_thumb.jpg</url_thumb>
                <is_default>true</is_default>
                <description>Main view</description>
            </image>
        </images>
        <external_created_at>2023-01-01T10:00:00+00:00</external_created_at>
        <external_updated_at>2023-01-01T12:00:00+00:00</external_updated_at>
    </property>
</response>
XML;

    expect($mockXmlResponse)->not->toBeEmpty('Mock XML string is empty.');

    $mockHandler = new MockHandler([
        new Response(200, ['Content-Type' => 'application/xml'], $mockXmlResponse),
    ]);
    $httpClient = new Client(['handler' => $mockHandler]);

    $apiKey = 'test_api_key';
    $username = 'test_username';
    $baseUrl = 'https://api.example.com';

    $api = new BookingManagerAPI($httpClient, $apiKey, $username, $baseUrl);

    $propertyId = 123;
    $response = $api->property($propertyId);

    expect($response)->toBeInstanceOf(PropertyResponse::class);
    expect($response->property)->toBeInstanceOf(PropertyDetails::class);
    expect($response->property->external_id)->toBe($propertyId);
    expect($response->property->name)->toBe('Test Property');
    expect($response->property->max_persons)->toBe(2);
    expect($response->property->external_created_at)->toBeInstanceOf(Carbon::class);
    expect($response->property->external_created_at->toIso8601String())->toBe('2023-01-01T10:00:00+00:00');
});

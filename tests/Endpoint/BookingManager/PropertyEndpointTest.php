<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\PropertyResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyDetails;
use Psr\Log\NullLogger;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Shelfwood\PhpPms\BookingManager\Enums\PropertyStatus;
use Shelfwood\PhpPms\BookingManager\Enums\ViewType;
use Shelfwood\PhpPms\BookingManager\Enums\InternetType;
use Shelfwood\PhpPms\BookingManager\Enums\InternetConnectionType;
use Shelfwood\PhpPms\BookingManager\Enums\ParkingType;
use Shelfwood\PhpPms\BookingManager\Enums\SwimmingPoolType;
use Shelfwood\PhpPms\BookingManager\Enums\SaunaType;

describe('PropertyEndpointTest', function () {
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

    test('BookingManagerAPI::property returns PropertyResponse with populated PropertyDetails', function () {
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/property-by-id.xml');
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->property(21663);

        expect($response)->toBeInstanceOf(PropertyResponse::class);
        expect($response->property)->toBeInstanceOf(PropertyDetails::class);
        expect($response->property->external_id)->toBe(21663);
        expect($response->property->name)->toBe('Runstraat suite Amsterdam');
        expect($response->property->identifier)->toBe('#487');
        expect($response->property->status)->toBeInstanceOf(PropertyStatus::class);
        expect($response->property->status->value)->toBe('live');
        expect($response->property->view)->toBeInstanceOf(ViewType::class);
        expect($response->property->view->value)->toBe('street');
        expect($response->property->internet)->toBeInstanceOf(InternetType::class);
        expect($response->property->internet->value)->toBe('wifi');
        expect($response->property->internet_connection)->toBeInstanceOf(InternetConnectionType::class);
        expect($response->property->internet_connection->value)->toBe('highspeed');
        expect($response->property->parking)->toBeInstanceOf(ParkingType::class);
        expect($response->property->parking->value)->toBe('none');
        expect($response->property->swimmingpool)->toBeInstanceOf(SwimmingPoolType::class);
        expect($response->property->swimmingpool->value)->toBe('none');
        expect($response->property->sauna)->toBeInstanceOf(SaunaType::class);
        expect($response->property->sauna->value)->toBe('none');
        expect($response->property->tax)->toBeInstanceOf(\Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyTax::class);
        expect($response->property->tax->otherType)->toBeInstanceOf(\Shelfwood\PhpPms\BookingManager\Enums\TaxType::class);
        expect($response->property->tax->otherType->value)->toBe('relative');
    });
    test('BookingManagerAPI::property throws HttpClientException on generic API error', function () {
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/generic-error.xml');
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);
        $this->api->property(1);
    })->throws(\Shelfwood\PhpPms\Exceptions\HttpClientException::class);

    });

<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\PropertiesResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyDetails;
use GuzzleHttp\ClientInterface;
use Psr\Log\NullLogger;
use Shelfwood\PhpPms\BookingManager\Enums\PropertyStatus;
use Shelfwood\PhpPms\BookingManager\Enums\ViewType;
use Shelfwood\PhpPms\BookingManager\Enums\InternetType;
use Shelfwood\PhpPms\BookingManager\Enums\InternetConnectionType;
use Shelfwood\PhpPms\BookingManager\Enums\ParkingType;
use Shelfwood\PhpPms\BookingManager\Enums\SwimmingPoolType;
use Shelfwood\PhpPms\BookingManager\Enums\SaunaType;
use Shelfwood\PhpPms\BookingManager\Enums\TaxType;
use Tests\Helpers\TestHelpers;

// Import the Golden Master assertion function
use function Tests\Helpers\assertPropertiesResponseMatchesExpected;


describe('PropertiesEndpointTest', function () {
    beforeEach(function () {
        $this->mockHttpClient = $this->createMock(ClientInterface::class);
        $this->api = new BookingManagerAPI(
            $this->mockHttpClient,
            'dummy-api-key',
            'https://dummy-url',
            new NullLogger()
        );
    });

    test('Golden Master: PropertiesResponse validates comprehensive property collection mapping', function () {
        $xml = file_get_contents(TestHelpers::getMockFilePath('all-properties.xml'));

        $mockResponse = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $mockStream = $this->createMock(\Psr\Http\Message\StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->properties();

        expect($response)->toBeInstanceOf(PropertiesResponse::class);

        // Use Golden Master validation for comprehensive property mapping
        assertPropertiesResponseMatchesExpected($response);
    });

    });

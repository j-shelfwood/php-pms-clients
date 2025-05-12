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


describe('PropertiesEndpointTest', function () {
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

    test('BookingManagerAPI::properties returns PropertiesResponse with PropertyDetails objects', function () {

        $xml = file_get_contents(TestHelpers::getMockFilePath('all-properties.xml'));

        $mockResponse = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $mockStream = $this->createMock(\Psr\Http\Message\StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->properties();

        expect($response)->toBeInstanceOf(PropertiesResponse::class);
        expect($response->properties)->toBeArray();
        expect(count($response->properties))->toBeGreaterThan(2); // We will check first 3 properties

        // Property 1 (ID 6743 from mock)
        $property1 = $response->properties[0];
        expect($property1)->toBeInstanceOf(PropertyDetails::class);
        expect($property1->external_id)->toBe(6743);
        expect($property1->status)->toBeInstanceOf(PropertyStatus::class);
        expect($property1->status->value)->toBe('live');
        expect($property1->view)->toBeNull();
        expect($property1->internet)->toBeNull();
        expect($property1->internet_connection)->toBeNull();
        expect($property1->parking)->toBeNull();
        expect($property1->swimmingpool)->toBeNull();
        expect($property1->sauna)->toBeNull();
        expect($property1->tax)->toBeInstanceOf(\Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyTax::class); // Tax object is always created
        expect($property1->tax->otherType)->toBeNull();

        // Property 2 (ID 6786 from mock)
        $property2 = $response->properties[1];
        expect($property2)->toBeInstanceOf(PropertyDetails::class);
        expect($property2->external_id)->toBe(6786);
        expect($property2->status)->toBeInstanceOf(PropertyStatus::class);
        expect($property2->status->value)->toBe('live');
        expect($property2->view)->toBeNull();
        expect($property2->internet)->toBeNull();
        expect($property2->internet_connection)->toBeNull();
        expect($property2->parking)->toBeNull();
        expect($property2->swimmingpool)->toBeNull();
        expect($property2->sauna)->toBeNull();
        expect($property2->tax)->toBeInstanceOf(\Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyTax::class); // Tax object is always created
        expect($property2->tax->otherType)->toBeNull();

        // Property 3 (ID 6794 from mock - has more details)
        $property3 = $response->properties[2];
        expect($property3)->toBeInstanceOf(PropertyDetails::class);
        expect($property3->external_id)->toBe(6794);
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

    });

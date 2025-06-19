<?php

declare(strict_types=1);

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\NullLogger;
use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\PropertyResponse;
use Shelfwood\PhpPms\Exceptions\ApiException;
use Tests\Helpers\TestData;
use Tests\Helpers\TestHelpers;

use function Tests\Helpers\assertPropertyDetails;

describe('PropertyEndpointTest', function () {
    beforeEach(function () {
        $this->mockHttpClient = $this->createMock(ClientInterface::class);
        $this->api = new BookingManagerAPI(
            $this->mockHttpClient,
            'dummy-api-key',
            'https://dummy-url',
            new NullLogger()
        );
    });

    test('it correctly maps various property responses', function (string $mockFile, array $expectedData) {
        $xml = file_get_contents(TestHelpers::getMockFilePath($mockFile));
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $response = $this->api->property($expectedData['external_id']);

        expect($response)->toBeInstanceOf(PropertyResponse::class);
        assertPropertyDetails($response->property, $expectedData);
    })->with([
        'rich property' => ['property-richest.xml', TestData::getExpectedRichestPropertyData()],
        'inactive property' => ['property-inactive.xml', TestData::getExpectedInactivePropertyData()],
        'minimal property' => ['property-minimal.xml', TestData::getExpectedMinimalPropertyData()],
        'standard property by id' => ['property-by-id.xml', TestData::getExpectedPropertyData()],
        'single image property' => ['property-single-image.xml', TestData::getExpectedSingleImagePropertyData()],
        'no images property' => ['property-no-images.xml', TestData::getExpectedNoImagesPropertyData()],
    ]);

    test('BookingManagerAPI::property throws ApiException on API error', function () {
        $xml = file_get_contents(TestHelpers::getMockFilePath('generic-error.xml'));
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        expect(fn() => $this->api->property(12345))
            ->toThrow(ApiException::class, 'Generic API Error');
    });
});

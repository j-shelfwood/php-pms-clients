<?php

declare(strict_types=1);

use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\RateResponse;
use Psr\Log\NullLogger;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Carbon\Carbon;


describe('RateForStayEndpointTest', function () {
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
    
    test('BookingManagerAPI::rateForStay returns RateResponse with correct values', function () {
        $xml = file_get_contents(__DIR__ . '/../../../mocks/bookingmanager/get-rate-for-stay.xml');
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockStream = $this->createMock(StreamInterface::class);
        $mockStream->method('getContents')->willReturn($xml);
        $mockResponse->method('getBody')->willReturn($mockStream);
        $this->mockHttpClient->method('request')->willReturn($mockResponse);
    
        $arrival = Carbon::parse('2024-02-19');
        $departure = Carbon::parse('2024-02-20');
        $response = $this->api->rateForStay(21663, $arrival, $departure, 1);
    
        expect($response)->toBeInstanceOf(RateResponse::class);
        expect($response->final_before_taxes)->toBeFloat()->toBe(220.0);
        expect($response->final_after_taxes)->toBeFloat()->toBe(255.20);
        expect($response->tax_vat)->toBeFloat()->toBe(19.80);
        expect($response->tax_other)->toBeFloat()->toBe(15.40);
        expect($response->tax_total)->toBeFloat()->toBe(35.20);
        expect($response->prepayment)->toBeFloat()->toBe(66.00);
        expect($response->balance_due_remaining)->toBeFloat()->toBe(189.20);
        expect($response->propertyId)->toBe(21663);
        expect($response->propertyIdentifier)->toBe('#487');
        expect($response->maxPersons)->toBe(2);
        expect($response->available)->toBeFalse();
        expect($response->minimalNights)->toBe(1);
    });
    
    });

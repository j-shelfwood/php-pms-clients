<?php

declare(strict_types=1);

use Domain\Connections\BookingManager\Mappers\BookingManagerMapper;
use Domain\Connections\BookingManager\Responses\CalendarChangesResponse;
use Domain\Connections\BookingManager\Responses\CalendarResponse;
use Domain\Connections\BookingManager\Responses\CreateBookingResponse;
use Domain\Connections\BookingManager\Responses\FinalizeBookingResponse;
use Domain\Connections\BookingManager\Responses\PropertyInfoResponse;
use Domain\Connections\BookingManager\Responses\RateResponse;
use Domain\Dtos\BookingRate;
use Domain\Dtos\CalendarChangesResponse as DtoCalendarChangesResponse;
use Domain\Dtos\CalendarResponse as DtoCalendarResponse;
use Domain\Dtos\CreateBookingResponse as DtoCreateBookingResponse;
use Domain\Dtos\PropertyInfoDto;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->mapper = new BookingManagerMapper;
});

describe('BookingManagerMapper', function () {
    it('maps RateResponse to BookingRate', function () {
        $raw = new RateResponse(220.00, 255.20, 19.80, 15.40, 35.20, 66.00, 189.20);
        $dto = $this->mapper->fromRawRate($raw);
        expect($dto)->toBeInstanceOf(BookingRate::class);
        expect($dto->final_before_taxes)->toBe(220.00);
        expect($dto->final_after_taxes)->toBe(255.20);
        expect($dto->tax_vat)->toBe(19.80);
        expect($dto->tax_tourist)->toBe(15.40);
        expect($dto->tax_total)->toBe(35.20);
        expect($dto->prepayment)->toBe(66.00);
        expect($dto->balance_due_remaining)->toBe(189.20);
    });

    it('maps PropertyInfoResponse to PropertyInfoDto', function () {
        $provider = new \Domain\Connections\BookingManager\Responses\ValueObjects\PropertyProvider(1, 'BM', 'BookingManager');
        $location = new \Domain\Connections\BookingManager\Responses\ValueObjects\PropertyLocation(52.37, 4.89, 'Fagelstraat 83H', '1052GA', 'Amsterdam', 'NL', null, null, '');
        $raw = new PropertyInfoResponse(
            6794, 'Canal Holiday apartment Amsterdam', 'ID-6794', 'active', ['apartment'],
            $provider, $location, 4, 2, 14, null, null, 1, false, 80.0, 2, 1, 1, 0, 0, 0, 1, false, null, null, null, false, false, true, false, false, null, null
        );
        $dto = $this->mapper->fromRawProperty($raw);
        expect($dto)->toBeInstanceOf(PropertyInfoDto::class);
        expect($dto->externalId)->toBe(6794);
        expect($dto->name)->toBe('Canal Holiday apartment Amsterdam');
        expect($dto->status)->toBe('active');
    });

    it('maps CalendarResponse to DtoCalendarResponse', function () {
        $raw = new CalendarResponse(21663, new Collection([]));
        $dto = $this->mapper->fromRawCalendar($raw);
        expect($dto)->toBeInstanceOf(DtoCalendarResponse::class);
    });

    it('maps CalendarChangesResponse to DtoCalendarChangesResponse', function () {
        $raw = new CalendarChangesResponse(2, now(), new Collection([]));
        $dto = $this->mapper->fromRawCalendarChanges($raw);
        expect($dto)->toBeInstanceOf(DtoCalendarChangesResponse::class);
    });

    it('maps CreateBookingResponse to DtoCreateBookingResponse', function () {
        $raw = new CreateBookingResponse(171830, 'BILL-171830-148-AMS-21663-2024-02-08', 'Booking created.');
        $dto = $this->mapper->fromRawCreateBooking($raw);
        expect($dto)->toBeInstanceOf(DtoCreateBookingResponse::class);
        expect($dto->booking_id)->toBe(171830);
        expect($dto->identifier)->toBe('BILL-171830-148-AMS-21663-2024-02-08');
    });

    it('handles finalize booking mapping (void)', function () {
        $raw = new FinalizeBookingResponse(171830, 'BILL-171830-148-AMS-21663-2024-02-08', 'Booking finalized.');
        $result = $this->mapper->fromRawFinalizeBooking($raw);
        expect($result)->toBeNull();
    });
});

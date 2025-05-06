<?php

declare(strict_types=1);

use App\Models\Booking;
use App\Models\Property;
use Domain\Connections\BookingManager\BookingManagerAPI;
use Domain\Dtos\BookingRate;
use Domain\Dtos\CalendarChangesResponse;
use Domain\Dtos\CalendarResponse;
use PhpPms\Clients\BookingManager\Payloads\CreateBookingPayload;
use Domain\Dtos\CreateBookingResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->bookingManager = new BookingManagerAPI('http://example.com', 'test_api_key');
});

describe('BookingManagerAPI', function () {
    it('gets rate for stay', function () {
        Http::fake([
            '*' => Http::response(file_get_contents(resource_path('mocks/get-rate-for-stay.xml')), 200),
        ]);
        $rate = $this->bookingManager->getRateForStay(21663, Carbon::parse('2024-02-19'), Carbon::parse('2024-02-20'));
        expect($rate)->toBeInstanceOf(BookingRate::class);
    });

    it('creates booking', function () {
        Http::fake([
            '*' => Http::response(file_get_contents(resource_path('mocks/create-booking.xml')), 200),
        ]);
        $bookingData = new CreateBookingPayload(
            start: '2024-02-08',
            end: '2024-02-12',
            name_first: 'Joris',
            name_last: 'Schelfhout',
            email: 'joris@shelfwood.co',
            address_1: 'Fagelstraat 83H',
            address_2: '1052GA',
            city: 'Amsterdam',
            country: '',
            phone: '+31648353484',
            amount_adults: 1,
            amount_childs: 0,
            time_arrival: '14:00:00',
            flight: '',
            notes: '[2024-01-05 20:47:22] Booking via www.hotelixamsterdam.com\nyes',
            property_id: 21663,
            balance_due: 1213.15,
        );
        $response = $this->bookingManager->createBooking($bookingData);
        expect($response)->toBeInstanceOf(CreateBookingResponse::class);
    });

    it('finalizes booking', function () {
        Http::fake([
            '*' => Http::response('<response>Success</response>', 200),
        ]);
        $booking = Booking::factory()->create(['external_id' => 171830]);
        $result = $this->bookingManager->finalizeBooking($booking);
        expect($result)->not()->toBeNull();
    });

    it('gets calendar changes', function () {
        Http::fake([
            '*' => Http::response(file_get_contents(resource_path('mocks/calendar-changes.xml')), 200),
        ]);
        $since = Carbon::parse('2023-11-11 12:00:00');
        $response = $this->bookingManager->getCalendarChanges($since);
        expect($response)->toBeInstanceOf(CalendarChangesResponse::class);
    });

    it('gets calendar for date range', function () {
        Http::fake([
            '*' => Http::response(file_get_contents(resource_path('mocks/calendar-date-range.xml')), 200),
        ]);
        $property = Property::factory()->create(['external_id' => '21663']);
        $startDate = Carbon::parse('2023-11-01');
        $endDate = Carbon::parse('2023-11-07');
        $response = $this->bookingManager->getCalendarForDateRange($property->external_id, $startDate, $endDate);
        expect($response)->toBeInstanceOf(CalendarResponse::class);
    });

    it('gets all properties', function () {
        Http::fake([
            '*' => Http::response(file_get_contents(resource_path('mocks/all-properties.xml')), 200),
        ]);
        $response = $this->bookingManager->getAllProperties();
        expect($response)->toBeInstanceOf(Collection::class);
        expect($response->first())->not()->toBeNull();
    });
});

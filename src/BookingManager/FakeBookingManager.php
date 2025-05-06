<?php

namespace PhpPms\Clients\BookingManager;

use App\Models\Booking;
use App\Models\Calendar;
use App\Models\Property;
use Domain\Dtos\BookingRate;
use Domain\Dtos\CalendarChange;
use Domain\Dtos\CalendarChangesResponse;
use Domain\Dtos\CalendarInfo;
use Domain\Dtos\CalendarResponse;
use Domain\Dtos\CreateBookingData;
use Domain\Dtos\CreateBookingResponse;
use Domain\Dtos\PropertyInfo;
use Domain\Dtos\PropertyResponse;
use Faker\Factory as Faker;
use Faker\Generator;
use Illuminate\Support\Carbon;

class FakeBookingManager extends BookingManagerAPI
{
    protected Generator $faker;

    public function __construct()
    {
        $this->faker = Faker::create();
    }

    public function getRateForStay(int $id, Carbon $arrival, Carbon $departure): BookingRate
    {
        return BookingRate::mock($id, $arrival, $departure);
    }

    public function createBooking(CreateBookingData $bookingData): CreateBookingResponse
    {
        return new CreateBookingResponse(
            response: $this->parseXml(file_get_contents(resource_path('mocks/create-booking.xml'))),
            booking_id: rand(100000, 999999),
            identifier: 'HotelIX-420',
        );
    }

    public function finalizeBooking(Booking $booking): void
    {
        // This is a mock implementation, so we don't need to do anything here.
    }

    public function getCalendarChanges(Carbon $since): CalendarChangesResponse
    {
        // Pick a random property and return a month in the coming 6 months
        $property = Property::inRandomOrder()->first();
        $month = now()->year.'-'.collect(range(now()->month, now()->addMonths(6)->month))->random();

        return new CalendarChangesResponse(
            amount: 1,
            time: now(),
            changes: collect([
                new CalendarChange(
                    id: $property->external_id,
                    months: [$month]
                ),
            ])
        );
    }

    public function getCalendarForDateRange(Property $property, Carbon $startDate, Carbon $endDate): CalendarResponse
    {
        $isEven = $property->id % 2 === 0;
        $minRate = $isEven ? 150 : 250;
        $maxRate = $isEven ? 300 : 500;

        return new CalendarResponse(
            calendars: collect(range(0, $endDate->diffInDays($startDate, true)))->map(function ($day) use ($property, $startDate, $minRate, $maxRate) {
                return new CalendarInfo(...Calendar::factory()->make([
                    'property_id' => $property->id,
                    'date' => $startDate->clone()->addDays($day),
                    'available' => true,
                    'final_rate' => app()->environment('testing') ? 100 : $this->faker->randomFloat(2, $minRate, $maxRate),
                ])->only(['date', 'season', 'available', 'stay_minimum', 'final_rate']));
            })
        );
    }

    public function getAllProperties(): PropertyResponse
    {
        return new PropertyResponse(
            properties: collect(range(1, 10))->map(fn () => PropertyInfo::factory()->create())
        );
    }

    public function getPropertyById(int $id): PropertyResponse
    {
        // Return a mock single property for the given id
        return new PropertyResponse(
            properties: collect([
                PropertyInfo::factory()->create(),
            ])
        );
    }
}

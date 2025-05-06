<?php

namespace PhpPms\Clients\BookingManager\Mappers;

use Domain\Connections\BookingManager\Responses\CalendarChangesResponse as RawCalendarChangesResponse;
use Domain\Connections\BookingManager\Responses\CalendarResponse as RawCalendarResponse;
use Domain\Connections\BookingManager\Responses\CreateBookingResponse as RawCreateBookingResponse;
use Domain\Connections\BookingManager\Responses\FinalizeBookingResponse as RawFinalizeBookingResponse;
use Domain\Connections\BookingManager\Responses\PropertyInfoResponse as RawPropertyInfoResponse;
use Domain\Connections\BookingManager\Responses\RateResponse as RawRateResponse;
use Domain\Dtos\BookingRate;
use Domain\Dtos\CalendarChangesResponse as DtoCalendarChangesResponse;
use Domain\Dtos\CalendarResponse as DtoCalendarResponse;
use Domain\Dtos\CreateBookingResponse as DtoCreateBookingResponse;
use Domain\Dtos\PropertyInfoDto;

class BookingManagerMapper
{
    public function fromRawRate(RawRateResponse $raw): BookingRate
    {
        return new BookingRate(
            final_before_taxes: $raw->final_before_taxes,
            final_after_taxes: $raw->final_after_taxes,
            tax_vat: $raw->tax_vat,
            tax_tourist: $raw->tax_other, // unified property name
            tax_total: $raw->tax_total,
            prepayment: $raw->prepayment,
            balance_due_remaining: $raw->balance_due_remaining
        );
    }

    public function fromRawProperty(RawPropertyInfoResponse $raw): PropertyInfoDto
    {
        return new PropertyInfoDto(
            externalId: $raw->external_id,
            name: $raw->name,
            status: $raw->status,
            type: $raw->property_types[0] ?? null,
            providerId: $raw->provider->id ?? null,
            street: $raw->location->address ?? null,
            zipcode: $raw->location->zipcode ?? null,
            city: $raw->location->city ?? null,
            country: $raw->location->country ?? null
        );
    }

    public function fromRawCalendar(RawCalendarResponse $raw): DtoCalendarResponse
    {
        // You must adapt this to your actual DtoCalendarResponse API
        return DtoCalendarResponse::fromResponse([
            'propertyId' => $raw->propertyId,
            'days' => $raw->days->toArray(),
        ]);
    }

    public function fromRawCalendarChanges(RawCalendarChangesResponse $raw): DtoCalendarChangesResponse
    {
        return DtoCalendarChangesResponse::map(new \Illuminate\Support\Collection([
            'amount' => $raw->amount,
            'time' => $raw->time,
            'changes' => $raw->changes,
        ]));
    }

    public function fromRawCreateBooking(RawCreateBookingResponse $raw): DtoCreateBookingResponse
    {
        return new DtoCreateBookingResponse(
            response: collect(['message' => $raw->message ?? null]),
            booking_id: $raw->bookingId,
            identifier: $raw->identifier
        );
    }

    public function fromRawFinalizeBooking(RawFinalizeBookingResponse $raw): void
    {
        // finalize has no return, nothing to map
    }
}

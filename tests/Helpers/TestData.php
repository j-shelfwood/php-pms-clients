<?php

namespace Tests\Helpers;

use Carbon\Carbon;

class TestData
{
    /**
     * Golden Master data for property-by-id.xml mock
     * Every field must match the exact structure and values from the mock XML.
     */
    public static function getExpectedPropertyData(): array
    {
        return [
            'external_id' => 21663,
            'name' => 'Runstraat suite Amsterdam',
            'identifier' => '#487',
            'status' => 'live',
            'property_types' => ['leisure', 'business'],
            'provider' => [
                'id' => 114,
                'code' => 'AMS',
                'name' => 'Billy Blue Amsterdam Hotels'
            ],
            'location' => [
                'latitude' => 52.3724,
                'longitude' => 4.88559,
                'address' => 'Hartenstraat',
                'zipcode' => '1016 CB',
                'city' => 'Amsterdam',
                'country' => 'NL',
                'cityLatitude' => 52.3731,
                'cityLongitude' => 4.89235,
                'area' => 'Jordaan Area'
            ],
            'max_persons' => 2,
            'minimal_nights' => 2,
            'maximal_nights' => 0,
            'available_start' => '2014-06-01',
            'available_end' => '2024-06-30',
            'floor' => 1,
            'stairs' => false,
            'size' => 32.0,
            'bedrooms' => 0,
            'single_bed' => 0,
            'double_bed' => 1,
            'single_sofa' => 0,
            'double_sofa' => 0,
            'single_bunk' => 0,
            'bathrooms' => 1,
            'toilets' => 1,
            'elevator' => false,
            'view' => 'street',
            'internet' => 'wifi',
            'internet_connection' => 'highspeed',
            'parking' => 'none',
            'airco' => false,
            'fans' => false,
            'balcony' => false,
            'patio' => false,
            'garden' => false,
            'roof_terrace' => false,
            'tv' => 'flatscreen',
            'tv_connection' => 'cable',
            'dvd' => 'none',
            'computer' => false,
            'printer' => false,
            'iron' => false,
            'dishwasher' => false,
            'oven' => false,
            'microwave' => false,
            'grill' => false,
            'hob' => false,
            'fridge' => true,
            'freezer' => false,
            'washingmachine' => false,
            'dryer' => false,
            'toaster' => false,
            'kettle' => true,
            'coffeemachine' => true,
            'bathtub' => 0,
            'jacuzzi' => 0,
            'shower_regular' => 1,
            'shower_steam' => 0,
            'swimmingpool' => 'none',
            'sauna' => 'none',
            'hairdryer' => true,
            'entresol' => false,
            'wheelchair_friendly' => false,
            'smoking_allowed' => false,
            'pets_allowed' => false,
            'heating' => true,
            'supplies' => [
                'coffee' => true,
                'tea' => true,
                'milk' => true,
                'sugar' => true,
                'dishwasherTablets' => false
            ],
            'service' => [
                'linen' => true,
                'towels' => true,
                'cleaning' => false // Default value when not specified
            ],
            'cleaning_costs' => 0.0,
            'deposit_costs' => 0.0,
            'check_in' => '14:00',
            'check_out' => '11:00',
            'tax' => [
                'vat' => 12.5,
                'other' => 9.0,
                'otherType' => 'relative'
            ],
            'prepayment' => 30.0,
            'fee' => 10.0, // fee type="percentage" with value 10
            'content' => [
                'short' => '',
                'full' => '',
                'area' => '',
                'arrival' => '',
                'termsAndConditions' => ''
            ],
            'images' => [
                [
                    'name' => '1.jpg',
                    'url' => 'http://control.bookingmanager.com/data/property/real/21663_1.jpg',
                    'modified' => '2022-04-28 11:08:17',
                    'description' => 'Double bed Runstraat suite Amsterdam'
                ],
                [
                    'name' => '2.jpg',
                    'url' => 'http://control.bookingmanager.com/data/property/real/21663_2.jpg',
                    'modified' => '2022-04-28 11:08:17',
                    'description' => 'Double bed other angle Runstraat suite Amsterdam'
                ],
                // We'll validate the first few images to keep assertions manageable
                // but the helper can check the total count
            ],
            'external_created_at' => '2014-01-15T15:14:54+00:00',
            'external_updated_at' => '2023-11-10T08:55:55+00:00'
        ];
    }

    /**
     * Golden Master data for get-rate-for-stay.xml mock
     */
    public static function getExpectedRateData(): array
    {
        return [
            'final_before_taxes' => 220.0,
            'final_after_taxes' => 255.20,
            'tax_vat' => 19.80,
            'tax_other' => 15.40,
            'tax_total' => 35.20,
            'prepayment' => 66.00,
            'balance_due_remaining' => 189.20,
            'propertyId' => 21663,
            'propertyIdentifier' => '#487',
            'maxPersons' => 2,
            'available' => false,
            'minimalNights' => 1
        ];
    }

    /**
     * Golden Master data for create-booking.xml mock
     */
    public static function getExpectedBookingData(): array
    {
        return [
            'id' => 171830,
            'identifier' => 'BILL-171830-148-AMS-21663-2024-02-08',
            'provider_identifier' => 'Provider-1234-148',
            'channel_identifier' => null,
            'arrival' => '2024-02-08',
            'departure' => '2024-02-12',
            'first_name' => 'Joris',
            'last_name' => 'Schelfhout',
            'email' => 'joris@shelfwood.co',
            'address_1' => 'Fagelstraat 83H',
            'address_2' => null,
            'city' => 'Amsterdam',
            'country' => 'NL',
            'phone' => '+31648353484',
            'amount_adults' => 1,
            'amount_children' => 0,
            'time_arrival' => null,
            'flight' => null,
            'notes' => null,
            'property_id' => 21663,
            'property_identifier' => '#487',
            'property_name' => 'Runstraat suite Amsterdam',
            'status' => 'open',
            'rate' => [
                'total' => 880.00,
                'final' => 880.00,
                'tax' => [
                    'total' => 0.0,
                    'vat' => 0.0,
                    'other' => 0.0,
                    'final' => 880.00
                ],
                'prepayment' => null,
                'balance_due' => null,
                'fee' => null
            ],
            'created' => '2024-02-07T10:12:34+00:00',
            'modified' => '2024-02-07T10:12:34+00:00'
        ];
    }

    /**
     * Golden Master data for calendar-date-range.xml mock
     */
    public static function getExpectedCalendarData(): array
    {
        return [
            'propertyId' => 22958,
            'days' => [
                [
                    'day' => '2023-11-01',
                    'season' => 'high',
                    'modified' => '2023-10-30T10:15:30+00:00',
                    'available' => 0,
                    'stayMinimum' => 3,
                    'rate' => [
                        'percentage' => 30.0,
                        'currency' => 'EUR',
                        'total' => 173.0,
                        'final' => 179.92,
                        'tax' => [
                            'total' => 6.92,
                            'other' => 0.0,
                            'otherType' => '',
                            'otherValue' => 0.0,
                            'vat' => 6.92,
                            'vatValue' => 4.0,
                            'final' => 179.92
                        ],
                        'fee' => 0.0,
                        'prepayment' => 53.98,
                        'balanceDue' => 125.94
                    ],
                    'maxStay' => null,
                    'closedOnArrival' => null,
                    'closedOnDeparture' => null,
                    'stopSell' => null
                ]
                // Additional days would be included for complete validation
            ]
        ];
    }
}
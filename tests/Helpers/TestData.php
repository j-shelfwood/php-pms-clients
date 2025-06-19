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
            // All 25 images from property-by-id.xml for comprehensive validation
            'images' => [
                ['name' => '1.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_1.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Double bed Runstraat suite Amsterdam'],
                ['name' => '2.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_2.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Double bed other angle Runstraat suite Amsterdam'],
                ['name' => '3.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_3.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Bed with the view of Sofa Runstraat suite Amsterdam'],
                ['name' => '4.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_4.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Sofa Runstraat suite Amsterdam'],
                ['name' => '5.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_5.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Sofa other angle Runstraat suite Amsterdam'],
                ['name' => '6.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_6.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Double bed other angle Runstraat suite Amsterdam'],
                ['name' => '7.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_7.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Window Runstraat suite Amsterdam'],
                ['name' => '8.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_8.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Bed Runstraat suite Amsterdam'],
                ['name' => '9.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_9.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Decorative details with the view of bathroom Runstraat suite Amsterdam'],
                ['name' => '10.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_10.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Bed other angle Runstraat suite Amsterdam'],
                ['name' => '11.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_11.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Television Runstraat suite Amsterdam'],
                ['name' => '12.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_12.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Shower Runstraat suite Amsterdam'],
                ['name' => '13.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_13.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Bathroom Runstraat suite Amsterdam'],
                ['name' => '14.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_14.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Bathroom other angle Runstraat suite Amsterdam'],
                ['name' => '15.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_15.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Entree Runstraat suite Amsterdam'],
                ['name' => '16.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_16.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Neighborhood Runstraat suite Amsterdam'],
                ['name' => '17.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_17.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Apartment building Runstraat suite Amsterdam'],
                ['name' => '18.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_18.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Apartment building other angle Runstraat suite Amsterdam'],
                ['name' => '19.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_19.jpg', 'modified' => '2022-04-28 11:08:20', 'description' => 'Door lock Runstraat suite Amsterdam'],
                ['name' => '20.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_20.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Neighborhood Runstraat suite Amsterdam'],
                ['name' => '21.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_21.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Water View Runstraat suite Amsterdam'],
                ['name' => '22.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_22.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Neighborhood other angle Runstraat suite Amsterdam'],
                ['name' => '23.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_23.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Others Runstraat suite Amsterdam'],
                ['name' => '24.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_24.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Neighborhood Runstraat suite Amsterdam'],
                ['name' => '25.jpg', 'url' => 'http://control.bookingmanager.com/data/property/real/21663_25.jpg', 'modified' => '2022-04-28 11:08:17', 'description' => 'Neighborhood Runstraat suite Amsterdam']
            ],
            'external_created_at' => '2014-01-15T15:14:54.000000Z',
            'external_updated_at' => '2023-11-10T08:55:55.000000Z'
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
            'provider_identifier' => '', // Empty in mock
            'channel_identifier' => '', // Empty in mock
            'arrival' => '2024-02-08',
            'departure' => '2024-02-12',
            'first_name' => 'Joris',
            'last_name' => 'Schelfhout',
            'email' => 'joris@shelfwood.co',
            'address_1' => 'Fagelstraat 83H',
            'address_2' => '1052GA',
            'city' => 'Amsterdam',
            'country' => '', // Empty in mock
            'phone' => '+31648353484',
            'amount_adults' => 1,
            'amount_children' => 0,
            'time_arrival' => '14:00:00',
            'flight' => '',
            'notes' => '[2024-01-05 20:47:22] Booking via www.hotelixamsterdam.com
    yes',
            'property_id' => 21663,
            'property_identifier' => '#487',
            'property_name' => 'Runstraat suite Amsterdam',
            'status' => 'open',
            'rate' => [
                'total' => 1232.00,
                'final' => 1410.64,
                'tax' => [
                    'total' => 225.70,
                    'vat' => 126.96,
                    'other' => 98.74,
                    'final' => 1636.34
                ],
                'prepayment' => 423.19,
                'balance_due' => 1213.15,
                'fee' => null
            ],
            'created' => '2024-01-05T20:47:22.000000Z',
            'modified' => '2024-01-05T20:47:22.000000Z'
        ];
    }

    /**
     * Golden Master data for edit-booking.xml mock
     */
    public static function getExpectedEditBookingData(): array
    {
        return [
            'id' => 16,
            'identifier' => 'BILL-16-AMSLOC-1723-2012-05-22',
            'provider_identifier' => 'Provider-1234-54353',
            'channel_identifier' => null,
            'arrival' => '2012-05-22',
            'departure' => '2012-05-26',
            'first_name' => 'tim',
            'last_name' => 'gerritsen',
            'email' => 'tim@mannetje.org',
            'address_1' => 'chassestraat 18',
            'address_2' => '',
            'city' => 'amsterdam',
            'country' => 'NL',
            'phone' => '+31617260066',
            'amount_adults' => 2,
            'amount_children' => 1,
            'time_arrival' => '14:00',
            'flight' => 'WZ2237',
            'notes' => '',
            'property_id' => 209,
            'property_identifier' => '1723',
            'property_name' => 'Amstel Studio 2',
            'status' => 'open',
            'rate' => [
                'total' => 550.00,
                'final' => 500.00,
                'tax' => [
                    'total' => 55.0,
                    'vat' => 25.0,
                    'other' => 30.0,
                    'final' => 555.0
                ],
                'prepayment' => 55.0,
                'balance_due' => 455.0,
                'fee' => 115.0
            ],
            'created' => '2011-11-03T21:45:46.000000Z',
            'modified' => '2011-11-03T21:55:28.000000Z'
        ];
    }

    /**
     * Golden Master data for view-booking.xml mock
     */
    public static function getExpectedViewBookingData(): array
    {
        return [
            'id' => 16,
            'identifier' => 'BILL-16-AMSLOC-1723-2012-05-22',
            'provider_identifier' => 'Provider-1234-54353',
            'channel_identifier' => null,
            'arrival' => '2012-05-22',
            'departure' => '2012-05-25', // Different from edit-booking
            'first_name' => 'tim',
            'last_name' => 'gerritsen',
            'email' => 'tim@mannetje.org',
            'address_1' => 'chassestraat 18',
            'address_2' => '',
            'city' => 'amsterdam',
            'country' => 'NL',
            'phone' => '+31617260066',
            'amount_adults' => 2,
            'amount_children' => 1,
            'time_arrival' => '14:00',
            'flight' => 'WZ2237',
            'notes' => '',
            'property_id' => 209,
            'property_identifier' => '1723',
            'property_name' => 'Amstel Studio 2',
            'status' => 'open',
            'rate' => [
                'total' => 550.00,
                'final' => 500.00,
                'tax' => [
                    'total' => 55.0,
                    'vat' => 25.0,
                    'other' => 30.0,
                    'final' => 555.0
                ],
                'prepayment' => 55.0,
                'balance_due' => 455.0,
                'fee' => 115.0
            ],
            'created' => '2011-11-03T21:45:46.000000Z',
            'modified' => '2011-11-03T21:45:46.000000Z'
        ];
    }

    /**
     * Golden Master data for pending-bookings.xml mock
     */
    public static function getExpectedPendingBookingsData(): array
    {
        return [
            'pendingBookings' => [
                [
                    'bookingId' => 16,
                    'status' => 'pending',
                    'guestName' => 'tim gerritsen'
                ]
            ]
        ];
    }

    /**
     * Golden Master data for cancel-booking.xml mock
     */
    public static function getExpectedCancelBookingData(): array
    {
        return [
            'id' => 171838,
            'identifier' => 'BILL-171838-148-AMS-21663-2024-02-08',
            'provider_identifier' => '',
            'channel_identifier' => '',
            'arrival' => '2024-02-08',
            'departure' => '2024-02-12',
            'first_name' => 'Joris',
            'last_name' => 'Schelfhout',
            'email' => 'joris@shelfwood.co',
            'address_1' => 'Fagelstraat 83H',
            'address_2' => '1052GA',
            'city' => 'Amsterdam',
            'country' => '',
            'phone' => '+31648353484',
            'amount_adults' => 1,
            'amount_children' => 0,
            'time_arrival' => '14:00:00',
            'flight' => '',
            'notes' => '[2024-01-06 14:46:22] Booking via www.hotelixamsterdam.com
yes',
            'property_id' => 21663,
            'property_identifier' => '#487',
            'property_name' => 'Runstraat suite Amsterdam',
            'status' => 'failed',
            'rate' => [
                'total' => 1232.00,
                'final' => 1410.64,
                'tax' => [
                    'total' => 225.70,
                    'vat' => 126.96,
                    'other' => 98.74,
                    'final' => 1636.34
                ],
                'prepayment' => 423.19,
                'balance_due' => 1213.15,
                'fee' => null
            ],
            'created' => '2024-01-06T14:46:22.000000Z',
            'modified' => '2024-01-06T14:46:29.000000Z'
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
                    'modified' => '2023-11-12T15:52:22.000000Z',
                    'available' => 0,
                    'stayMinimum' => 3,
                    'rate' => [
                        'percentage' => 104.0,
                        'currency' => 'EUR',
                        'total' => 173.0,
                        'final' => 179.92,
                        'tax' => [
                            'total' => 34.1848,
                            'other' => 17.99,
                            'otherType' => 'relative',
                            'otherValue' => 10.0,
                            'vat' => 16.19,
                            'vatValue' => 9.0,
                            'final' => 214.10
                        ],
                        'fee' => 0.0,
                        'prepayment' => 53.98,
                        'balanceDue' => 160.12
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

    /**
     * Golden Master data for calendar-changes.xml mock
     */
    public static function getExpectedCalendarChangesData(): array
    {
        return [
            'amount' => 2,
            'time' => '2023-11-12T12:00:00.000000Z',
            'changes' => [
                [
                    'propertyId' => 22958,
                    'months' => ['2023-11', '2023-12', '2024-01', '2024-02', '2024-03', '2024-04', '2024-05', '2024-06', '2024-07', '2024-08', '2024-09', '2024-10', '2024-11']
                ],
                [
                    'propertyId' => 23180,
                    'months' => ['2024-02', '2024-03', '2024-04', '2024-05', '2024-06', '2024-07', '2024-08', '2024-09', '2024-10', '2024-11']
                ]
            ]
        ];
    }

    /**
     * Golden Master data for all-properties.xml mock (focusing on property 6794 which has comprehensive data)
     */
    public static function getExpectedPropertiesData(): array
    {
        return [
            'totalProperties' => 308,
            'detailedProperty' => [  // Property 6794 from the mock
                'external_id' => 6794,
                'name' => 'Canal Holiday apartment Amsterdam',
                'identifier' => '#053',
                'status' => 'live',
                'property_types' => ['leisure', 'business'],
                'max_persons' => 4,
                'minimal_nights' => 3,
                'view' => 'water',
                'internet' => 'wifi',
                'internet_connection' => 'highspeed',
                'parking' => 'public',
                'swimmingpool' => 'none',
                'sauna' => 'none',
                'check_in' => '15:00',
                'check_out' => '10:00',
                'fee' => 10.0,
                'deposit_costs' => 150.0,
                'prepayment' => 12.1,
                'floor' => 2,
                'stairs' => 1,
                'size' => 90,
                'bedrooms' => 1,
                'single_bed' => 2,
                'double_bed' => 1,
                'double_sofa' => 1,
                'bathrooms' => 1,
                'toilets' => 1,
                'tax' => [
                    'vat' => 9.0,
                    'other' => 12.5,
                    'otherType' => 'relative'
                ]
            ]
        ];
    }

    /**
     * Golden Master data for single image property mock (property-single-image.xml)
     */
    public static function getExpectedSingleImagePropertyData(): array
    {
        return [
            'external_id' => 22098,
            'name' => 'Single Image Test Property',
            'identifier' => '#testSingle',
            'status' => 'live',
            'property_types' => ['leisure', 'business'],
            'provider' => [
                'id' => 93,
                'code' => 'AMS',
                'name' => 'Billy Blue Amsterdam'
            ],
            'location' => [
                'latitude' => 52.3568,
                'longitude' => 4.89784,
                'address' => 'Test Street',
                'zipcode' => '1073BR',
                'city' => 'Amsterdam',
                'country' => 'NL',
                'cityLatitude' => 52.3731,
                'cityLongitude' => 4.89235,
                'area' => 'Test Area'
            ],
            'max_persons' => 2,
            'minimal_nights' => 1,
            'maximal_nights' => 0,
            'available_start' => '2014-07-02',
            'available_end' => '2025-05-05',
            'floor' => 1,
            'stairs' => false,
            'size' => 30.0,
            'bedrooms' => 1,
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
            'microwave' => true,
            'grill' => false,
            'hob' => true,
            'fridge' => true,
            'freezer' => false,
            'washingmachine' => false,
            'dryer' => false,
            'toaster' => false,
            'kettle' => true,
            'coffeemachine' => false,
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
                'coffee' => false,
                'tea' => true,
                'milk' => false,
                'sugar' => true,
                'dishwasherTablets' => false
            ],
            'service' => [
                'linen' => true,
                'towels' => true,
                'cleaning' => false
            ],
            'cleaning_costs' => 25.0,
            'deposit_costs' => 50.0,
            'check_in' => '15:00',
            'check_out' => '11:00',
            'tax' => [
                'vat' => 9.0,
                'other' => 6.0,
                'otherType' => 'relative'
            ],
            'prepayment' => 30.0,
            'fee' => 5.0,
            'content' => [
                'short' => 'A simple test property with exactly one image for testing XML parsing edge cases.',
                'full' => '<p><strong>A simple test property with exactly one image for testing XML parsing edge cases.</strong></p>',
                'area' => '<p>Test area description.</p>',
                'arrival' => '<p>Test arrival instructions.</p>',
                'termsAndConditions' => '<p>Test terms and conditions.</p>'
            ],
            'images' => [
                [
                    'name' => 'single_test.jpg',
                    'url' => 'http://control.bookingmanager.com/data/property/real/test_single.jpg',
                    'modified' => '2024-01-01 12:00:00',
                    'description' => 'Single Image Test Property'
                ]
            ],
            'external_created_at' => '2024-01-01T12:00:00.000000Z',
            'external_updated_at' => '2024-01-01T12:00:00.000000Z'
        ];
    }

    /**
     * Golden Master data for property-richest.xml mock
     */
    public static function getExpectedRichestPropertyData(): array
    {
        return [
            'external_id' => 22097,
            'name' => 'Albert Cuyp III B studio Amsterdam',
            'identifier' => '#622',
            'status' => 'live',
            'property_types' => ['leisure', 'business'],
            'provider' => [
                'id' => 93,
                'code' => 'AMS',
                'name' => 'Billy Blue Amsterdam'
            ],
            'location' => [
                'latitude' => 52.3568,
                'longitude' => 4.89784,
                'address' => 'Albert Cuypstraat',
                'zipcode' => '1073BR',
                'city' => 'Amsterdam',
                'country' => 'NL',
                'cityLatitude' => 52.3731,
                'cityLongitude' => 4.89235,
                'area' => 'De Pijp'
            ],
            'max_persons' => 2,
            'minimal_nights' => 3,
            'maximal_nights' => 0,
            'available_start' => '2014-07-02',
            'available_end' => '2025-05-05',
            'floor' => 3,
            'stairs' => false,
            'size' => 45.0,
            'bedrooms' => 0,
            'single_bed' => 2,
            'double_bed' => 0,
            'single_sofa' => 0,
            'double_sofa' => 0,
            'single_bunk' => 0,
            'bathrooms' => 1,
            'toilets' => 1,
            'elevator' => false,
            'view' => 'street',
            'internet' => 'wifi',
            'internet_connection' => 'highspeed',
            'parking' => 'public',
            'airco' => false,
            'fans' => true,
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
            'washingmachine' => true,
            'dryer' => false,
            'toaster' => true,
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
                'cleaning' => false
            ],
            'cleaning_costs' => 45.0,
            'deposit_costs' => 100.0,
            'check_in' => '14:00',
            'check_out' => '11:00',
            'tax' => [
                'vat' => 9.0,
                'other' => 12.5,
                'otherType' => 'relative'
            ],
            'prepayment' => 12.1,
            'fee' => 10.0,
            'content' => [
                'short' => 'An intimite and cosy studio in Amsterdam, for two people, just a short journey from the centre and the Heineken brewery.',
                'full' => '<p><strong>An intimite and cosy studio in Amsterdam, for two people, just a short journey from the centre and the Heineken brewery. </strong></p>',
                'area' => '<p>The location of this apartment is premium, with easy access to the Museumplein, with the van Gogh Museum and the Rijksmuseum.</p>',
                'arrival' => '<p>Dear guest,<br />Thank you for booking your Amsterdam apartment with us!</p>',
                'termsAndConditions' => '<p class="MsoNoSpacing"><strong><span style="text-decoration-line: underline;">Cancellation policy</span></strong></p>'
            ],
            'images' => [
                [
                    'name' => 'photos 00.jpg',
                    'url' => 'http://control.bookingmanager.com/data/property/real/22097_photos%2000.jpg',
                    'modified' => '2017-11-27 08:03:40',
                    'description' => 'Sitting Area Albert Cuyp III B studio Amsterdam'
                ],
                [
                    'name' => 'photos 01.jpg',
                    'url' => 'http://control.bookingmanager.com/data/property/real/22097_photos%2001.jpg',
                    'modified' => '2018-07-06 10:16:19',
                    'description' => 'Dining Area and kitchen Albert Cuyp III B studio Amsterdam'
                ],
                [
                    'name' => 'photos 002.jpg',
                    'url' => 'http://control.bookingmanager.com/data/property/real/22097_photos%20002.jpg',
                    'modified' => '2018-07-24 13:19:20',
                    'description' => 'Coffee machine and kettle Albert Cuyp III B studio Amsterdam'
                ]
            ],
            'external_created_at' => '2014-06-19T08:07:09.000000Z',
            'external_updated_at' => '2024-08-29T14:23:21.000000Z'
        ];
    }

    /**
     * Golden Master data for property-inactive.xml mock
     */
    public static function getExpectedInactivePropertyData(): array
    {
        return [
            'external_id' => 6861,
            'name' => 'Plantage Park suite Amsterdam',
            'identifier' => 'BBA0318',
            'status' => 'inactive',
            'property_types' => [],
            'provider' => [
                'id' => 0,
                'code' => '',
                'name' => ''
            ],
            'location' => [
                'latitude' => null,
                'longitude' => null,
                'address' => '',
                'zipcode' => '',
                'city' => '',
                'country' => '',
                'cityLatitude' => null,
                'cityLongitude' => null,
                'area' => ''
            ],
            'max_persons' => 0,
            'minimal_nights' => 0,
            'maximal_nights' => 0,
            'available_start' => null,
            'available_end' => null,
            'floor' => 0,
            'stairs' => false,
            'size' => null,
            'bedrooms' => 0,
            'single_bed' => 0,
            'double_bed' => 0,
            'single_sofa' => 0,
            'double_sofa' => 0,
            'single_bunk' => 0,
            'bathrooms' => 0,
            'toilets' => 0,
            'elevator' => false,
            'view' => null,
            'internet' => null,
            'internet_connection' => null,
            'parking' => null,
            'airco' => false,
            'fans' => false,
            'balcony' => false,
            'patio' => false,
            'garden' => false,
            'roof_terrace' => false,
            'tv' => null,
            'tv_connection' => null,
            'dvd' => null,
            'computer' => false,
            'printer' => false,
            'iron' => false,
            'dishwasher' => false,
            'oven' => false,
            'microwave' => false,
            'grill' => false,
            'hob' => false,
            'fridge' => false,
            'freezer' => false,
            'washingmachine' => false,
            'dryer' => false,
            'toaster' => false,
            'kettle' => false,
            'coffeemachine' => false,
            'bathtub' => 0,
            'jacuzzi' => 0,
            'shower_regular' => 0,
            'shower_steam' => 0,
            'swimmingpool' => null,
            'sauna' => null,
            'hairdryer' => false,
            'entresol' => false,
            'wheelchair_friendly' => false,
            'smoking_allowed' => false,
            'pets_allowed' => false,
            'heating' => false,
            'supplies' => [
                'coffee' => false,
                'tea' => false,
                'milk' => false,
                'sugar' => false,
                'dishwasherTablets' => false
            ],
            'service' => [
                'linen' => false,
                'towels' => false,
                'cleaning' => false
            ],
            'cleaning_costs' => 0.0,
            'deposit_costs' => 0.0,
            'check_in' => null,
            'check_out' => null,
            'tax' => [
                'vat' => 0.0,
                'other' => 0.0,
                'otherType' => null
            ],
            'prepayment' => null,
            'fee' => null,
            'content' => [
                'short' => '',
                'full' => '',
                'area' => '',
                'arrival' => '',
                'termsAndConditions' => ''
            ],
            'images' => [],
            'external_created_at' => null,
            'external_updated_at' => null
        ];
    }

    /**
     * Golden Master data for property-minimal.xml mock
     */
    public static function getExpectedMinimalPropertyData(): array
    {
        return [
            'external_id' => 6743,
            'name' => 'Historical Heart apartment Amsterdam',
            'identifier' => 'BBA0052',
            'status' => 'live',
            'property_types' => [],
            'provider' => [
                'id' => 0,
                'code' => '',
                'name' => ''
            ],
            'location' => [
                'latitude' => null,
                'longitude' => null,
                'address' => '',
                'zipcode' => '',
                'city' => '',
                'country' => '',
                'cityLatitude' => null,
                'cityLongitude' => null,
                'area' => ''
            ],
            'max_persons' => 0,
            'minimal_nights' => 0,
            'maximal_nights' => 0,
            'available_start' => null,
            'available_end' => null,
            'floor' => 0,
            'stairs' => false,
            'size' => null,
            'bedrooms' => 0,
            'single_bed' => 0,
            'double_bed' => 0,
            'single_sofa' => 0,
            'double_sofa' => 0,
            'single_bunk' => 0,
            'bathrooms' => 0,
            'toilets' => 0,
            'elevator' => false,
            'view' => null,
            'internet' => null,
            'internet_connection' => null,
            'parking' => null,
            'airco' => false,
            'fans' => false,
            'balcony' => false,
            'patio' => false,
            'garden' => false,
            'roof_terrace' => false,
            'tv' => null,
            'tv_connection' => null,
            'dvd' => null,
            'computer' => false,
            'printer' => false,
            'iron' => false,
            'dishwasher' => false,
            'oven' => false,
            'microwave' => false,
            'grill' => false,
            'hob' => false,
            'fridge' => false,
            'freezer' => false,
            'washingmachine' => false,
            'dryer' => false,
            'toaster' => false,
            'kettle' => false,
            'coffeemachine' => false,
            'bathtub' => 0,
            'jacuzzi' => 0,
            'shower_regular' => 0,
            'shower_steam' => 0,
            'swimmingpool' => null,
            'sauna' => null,
            'hairdryer' => false,
            'entresol' => false,
            'wheelchair_friendly' => false,
            'smoking_allowed' => false,
            'pets_allowed' => false,
            'heating' => false,
            'supplies' => [
                'coffee' => false,
                'tea' => false,
                'milk' => false,
                'sugar' => false,
                'dishwasherTablets' => false
            ],
            'service' => [
                'linen' => false,
                'towels' => false,
                'cleaning' => false
            ],
            'cleaning_costs' => 0.0,
            'deposit_costs' => 0.0,
            'check_in' => null,
            'check_out' => null,
            'tax' => [
                'vat' => 0.0,
                'other' => 0.0,
                'otherType' => null
            ],
            'prepayment' => null,
            'fee' => null,
            'content' => [
                'short' => '',
                'full' => '',
                'area' => '',
                'arrival' => '',
                'termsAndConditions' => ''
            ],
            'images' => [],
            'external_created_at' => null,
            'external_updated_at' => null
        ];
    }

    /**
     * Golden Master data for property-no-images.xml mock
     */
    public static function getExpectedNoImagesPropertyData(): array
    {
        return [
            'external_id' => 99999,
            'name' => 'No Image Test Property',
            'identifier' => '#noimage',
            'status' => 'live',
            'property_types' => ['leisure', 'business'],
            'provider' => [
                'id' => 93,
                'code' => 'AMS',
                'name' => 'Billy Blue Amsterdam'
            ],
            'location' => [
                'latitude' => 52.3568,
                'longitude' => 4.89784,
                'address' => 'Test Street',
                'zipcode' => '1073BR',
                'city' => 'Amsterdam',
                'country' => 'NL',
                'cityLatitude' => 52.3731,
                'cityLongitude' => 4.89235,
                'area' => 'Test Area'
            ],
            'max_persons' => 2,
            'minimal_nights' => 1,
            'maximal_nights' => 0,
            'available_start' => '2014-07-02',
            'available_end' => '2025-05-05',
            'floor' => 1,
            'stairs' => false,
            'size' => 30.0,
            'bedrooms' => 1,
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
            'microwave' => true,
            'grill' => false,
            'hob' => true,
            'fridge' => true,
            'freezer' => false,
            'washingmachine' => false,
            'dryer' => false,
            'toaster' => false,
            'kettle' => true,
            'coffeemachine' => false,
            'bathtub' => 0,
            'jacuzzi' => 0,
            'shower_regular' => 1,
            'shower_steam' => 0,
            'swimmingpool' => 'none',
            'sauna' => 'none',
            'hairdryer' => false,
            'entresol' => false,
            'wheelchair_friendly' => false,
            'smoking_allowed' => false,
            'pets_allowed' => false,
            'heating' => true,
            'supplies' => [
                'coffee' => false,
                'tea' => false,
                'milk' => false,
                'sugar' => false,
                'dishwasherTablets' => false
            ],
            'service' => [
                'linen' => false,
                'towels' => false,
                'cleaning' => false
            ],
            'cleaning_costs' => 0.0,
            'deposit_costs' => 0.0,
            'check_in' => '15:00',
            'check_out' => '11:00',
            'tax' => [
                'vat' => 21.0,
                'other' => 5.0,
                'otherType' => 'relative'
            ],
            'prepayment' => 50.0,
            'fee' => 25.0,
            'content' => [
                'short' => 'Basic property with no images',
                'full' => 'A simple property for testing purposes with no images',
                'area' => 'Test area description',
                'arrival' => 'Standard arrival instructions',
                'termsAndConditions' => 'Standard terms and conditions'
            ],
            'images' => [], // The key assertion - empty array
            'external_created_at' => '2024-01-01T10:00:00.000000Z',
            'external_updated_at' => '2024-01-01T10:00:00.000000Z'
        ];
    }
}
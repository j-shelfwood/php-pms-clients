<?php

namespace Shelfwood\PhpPms\BookingManager\Enums;

enum ChangeType: string
{
    case AVAILABILITY = 'availability';
    case RATE = 'rate';
    case PROVIDERS = 'providers';
    case BOOKINGS = 'bookings';
}

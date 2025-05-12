<?php

namespace Shelfwood\PhpPms\BookingManager\Enums;

enum ParkingType: string
{
    case PRIVATE = 'private';
    case FREE = 'free';
    case PUBLIC = 'public';
    case NONE = 'none';
}

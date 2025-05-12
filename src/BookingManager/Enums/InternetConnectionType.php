<?php

namespace Shelfwood\PhpPms\BookingManager\Enums;

enum InternetConnectionType: string
{
    case HIGHSPEED = 'highspeed';
    case MOBILE = 'mobile';
    case NONE = 'none';
}

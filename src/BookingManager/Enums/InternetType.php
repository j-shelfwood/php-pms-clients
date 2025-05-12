<?php

namespace Shelfwood\PhpPms\BookingManager\Enums;

enum InternetType: string
{
    case CABLE = 'cable';
    case WIFI = 'wifi';
    case USB = 'usb';
    case NONE = 'none';
}

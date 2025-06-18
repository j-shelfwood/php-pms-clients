<?php

namespace Shelfwood\PhpPms\BookingManager\Enums;

enum PropertyStatus: string
{
    case READY = 'ready';
    case LIVE = 'live';
    case INACTIVE = 'inactive';
}

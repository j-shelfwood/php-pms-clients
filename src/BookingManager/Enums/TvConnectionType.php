<?php

namespace Shelfwood\PhpPms\BookingManager\Enums;

enum TvConnectionType: string
{
    case SATELLITE = 'satellite';
    case CABLE = 'cable';
    case NONE = 'none';
}

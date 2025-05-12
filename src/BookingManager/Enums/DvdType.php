<?php

namespace Shelfwood\PhpPms\BookingManager\Enums;

enum DvdType: string
{
    case SURROUND = 'surround';
    case DVD = 'dvd';
    case CD = 'cd';
    case NONE = 'none';
}

<?php

namespace Shelfwood\PhpPms\BookingManager\Enums;

enum ViewType: string
{
    case WATER = 'water';
    case STREET = 'street';
    case FOREST = 'forest';
    case CITY = 'city';
}

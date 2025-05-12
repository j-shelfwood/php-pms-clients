<?php

namespace Shelfwood\PhpPms\BookingManager\Enums;

enum BookingStatus: string
{
    case PENDING = 'pending';
    case OPEN = 'open';
    case ERROR = 'error';
    case SUCCESS = 'success';
    case CANCELLED = 'cancelled';
}

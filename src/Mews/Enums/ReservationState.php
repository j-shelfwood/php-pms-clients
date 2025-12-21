<?php

namespace Shelfwood\PhpPms\Mews\Enums;

enum ReservationState: string
{
    case Inquired = 'Inquired';
    case Optional = 'Optional';
    case Confirmed = 'Confirmed';
    case Started = 'Started';
    case Processed = 'Processed';
    case Canceled = 'Canceled';
    case Requested = 'Requested';
}

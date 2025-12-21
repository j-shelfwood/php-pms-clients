<?php

namespace Shelfwood\PhpPms\Mews\Enums;

enum RateType: string
{
    case Public = 'Public';
    case Private = 'Private';
    case AvailabilityBlock = 'AvailabilityBlock';
}

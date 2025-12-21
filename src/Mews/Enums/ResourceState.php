<?php

namespace Shelfwood\PhpPms\Mews\Enums;

enum ResourceState: string
{
    case Dirty = 'Dirty';
    case Clean = 'Clean';
    case Inspected = 'Inspected';
    case OutOfService = 'OutOfService';
    case OutOfOrder = 'OutOfOrder';
}

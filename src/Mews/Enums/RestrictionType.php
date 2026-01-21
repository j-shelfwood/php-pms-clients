<?php

namespace Shelfwood\PhpPms\Mews\Enums;

/**
 * Mews Restriction Types
 *
 * Types of restrictions that can be applied to services and resources.
 *
 * @see https://mews-systems.gitbook.io/connector-api/operations/restrictions
 */
enum RestrictionType: string
{
    /** Restriction applied at the start of a reservation */
    case Start = 'Start';

    /** Restriction applied during the stay period */
    case Stay = 'Stay';

    /** Restriction applied at the end of a reservation */
    case End = 'End';
}

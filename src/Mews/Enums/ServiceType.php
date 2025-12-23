<?php

namespace Shelfwood\PhpPms\Mews\Enums;

/**
 * Mews Service Types
 *
 * Types of services available in Mews PMS.
 *
 * @see https://mews-systems.gitbook.io/connector-api/operations/services
 */
enum ServiceType: string
{
    /** Accommodation/lodging service (primary bookable service) */
    case Accommod = 'Accommod';

    /** Additional services that can be ordered */
    case Additional = 'Additional';

    /** Bookable service */
    case Bookable = 'Bookable';

    /** Orderable service */
    case Orderable = 'Orderable';

    /** Reservable service */
    case Reservable = 'Reservable';
}

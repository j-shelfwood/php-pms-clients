<?php

namespace Shelfwood\PhpPms\Mews\Enums;

/**
 * Service availability metrics (ver 2024-01-22)
 *
 * @see https://api.mews.com/Swagger/connector/swagger.yaml
 */
enum ResourceAvailabilityMetricType: string
{
    case OutOfOrderBlocks = 'OutOfOrderBlocks';
    case PublicAvailabilityAdjustment = 'PublicAvailabilityAdjustment';
    case OtherServiceReservationCount = 'OtherServiceReservationCount';
    case Occupied = 'Occupied';
    case ConfirmedReservations = 'ConfirmedReservations';
    case OptionalReservations = 'OptionalReservations';
    case BlockAvailability = 'BlockAvailability';
    case AllocatedBlockAvailability = 'AllocatedBlockAvailability';
    case UsableResources = 'UsableResources';
    case ActiveResources = 'ActiveResources';
}


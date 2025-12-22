<?php

namespace Shelfwood\PhpPms\Mews\Enums;

/**
 * Mews Connector API Age Classification
 *
 * Classification types for age categories as defined by Mews API.
 * Use age ranges within Child classification to distinguish infants/babies (e.g., 0-2 years).
 *
 * @see https://mews-systems.gitbook.io/connector-api/operations/agecategories
 */
enum AgeClassification: string
{
    case Adult = 'Adult';
    case Child = 'Child';
}

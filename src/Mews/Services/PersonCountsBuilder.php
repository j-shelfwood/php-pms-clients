<?php

namespace Shelfwood\PhpPms\Mews\Services;

use Shelfwood\PhpPms\Mews\MewsConnectorAPI;

/**
 * Mews Person Counts Builder
 *
 * Builds PersonCounts array for Mews API reservation requests from simplified adult/children counts.
 * Automatically fetches and maps age categories from Mews API.
 *
 * PersonCounts structure:
 * [
 *     ['AgeCategoryId' => 'adult-uuid', 'Count' => 2],
 *     ['AgeCategoryId' => 'child-uuid', 'Count' => 1],
 * ]
 *
 * @see https://mews-systems.gitbook.io/connector-api/operations/reservations#person-counts
 */
class PersonCountsBuilder
{
    /**
     * Build PersonCounts array from adult and children counts
     *
     * @param MewsConnectorAPI $client Mews API client
     * @param string $serviceId Service UUID
     * @param int $adults Number of adults (0-99)
     * @param int $children Number of children (0-99)
     * @return array PersonCounts array for Mews API
     * @throws \RuntimeException If required age categories not found
     */
    public static function fromAdultsChildren(
        MewsConnectorAPI $client,
        string $serviceId,
        int $adults,
        int $children = 0
    ): array {
        $personCounts = [];

        // Add adult count
        if ($adults > 0) {
            $adultCategory = $client->getAdultAgeCategory($serviceId);

            if (!$adultCategory) {
                throw new \RuntimeException(
                    "No active Adult age category found for service {$serviceId}. " .
                    "Ensure age categories are configured in Mews."
                );
            }

            $personCounts[] = [
                'AgeCategoryId' => $adultCategory->id,
                'Count' => $adults,
            ];
        }

        // Add children count
        if ($children > 0) {
            $childCategory = $client->getChildAgeCategory($serviceId);

            if (!$childCategory) {
                throw new \RuntimeException(
                    "No active Child age category found for service {$serviceId}. " .
                    "Ensure age categories are configured in Mews."
                );
            }

            $personCounts[] = [
                'AgeCategoryId' => $childCategory->id,
                'Count' => $children,
            ];
        }

        if (empty($personCounts)) {
            throw new \InvalidArgumentException(
                'At least one adult or child must be specified for reservation'
            );
        }

        return $personCounts;
    }
}

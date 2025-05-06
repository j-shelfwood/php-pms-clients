<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses;

use Tightenco\Collect\Support\Collection;
use Exception;

class DetailsResponse
{
    // Add properties here based on what details are expected
    // For example:
    // public readonly ?string $someDetail;

    public function __construct(/* define properties here */) {
        // $this->someDetail = $someDetail;
    }

    public static function map(Collection|array $rawResponse): self
    {
        try {
            $sourceData = $rawResponse instanceof Collection ? $rawResponse : new Collection($rawResponse);
            // Logic to extract details from $sourceData
            // For example:
            // $someDetail = $sourceData->get('someDetailAttribute');

            return new self(/* pass extracted properties here */);
        } catch (Exception $e) {
            throw new Exception('Failed to map DetailsResponse: ' . $e->getMessage(), 0, $e);
        }
    }
}

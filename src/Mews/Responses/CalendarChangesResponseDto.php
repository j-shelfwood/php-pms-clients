<?php

namespace Domain\Connections\Mews\Dtos;

use Domain\Dtos\CalendarChangesResponse;

class CalendarChangesResponseDto
{
    private array $raw;

    private function __construct(array $raw)
    {
        $this->raw = $raw;
    }

    public static function fromArray(array $raw): self
    {
        return new self($raw);
    }

    /**
     * Transform Mews calendar changes JSON into domain CalendarChangesResponse.
     */
    public function toDomain(): CalendarChangesResponse
    {
        // Assuming $raw structure: ['changes' => [...], 'updatedAt' => '...']
        $mapped = [
            'amount' => count($this->raw['items'] ?? []),
            'time' => $this->raw['updatedAt'] ?? null,
            'change' => collect($this->raw['items'] ?? [])->map(fn ($item) => [
                'type' => $item['type'],
                'id' => $item['id'],
                'time' => $item['timestamp'],
            ])->toArray(),
        ];

        return CalendarChangesResponse::fromResponse($mapped);
    }
}

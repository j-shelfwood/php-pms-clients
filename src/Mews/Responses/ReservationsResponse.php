<?php

namespace Shelfwood\PhpPms\Mews\Responses;

use Illuminate\Support\Collection;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Reservation;

class ReservationsResponse
{
    /**
     * @param Collection<int, Reservation> $items
     * @param string|null $cursor
     */
    public function __construct(
        public readonly Collection $items,
        public readonly ?string $cursor = null
    ) {}

    public static function map(array $data): self
    {
        return new self(
            items: collect($data['Reservations'] ?? [])
                ->map(fn($item) => Reservation::map($item['Reservation'] ?? $item)),
            cursor: $data['Cursor'] ?? null
        );
    }
}

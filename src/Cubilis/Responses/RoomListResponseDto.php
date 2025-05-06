<?php

namespace Domain\Connections\Cubilis\Dtos;

use Domain\Dtos\PropertyInfo;
use Domain\Dtos\PropertyListResponse;
use Illuminate\Support\Collection;

class RoomListResponseDto
{
    private Collection $raw;

    private function __construct(Collection $raw)
    {
        $this->raw = $raw;
    }

    public static function fromCollection(Collection $raw): self
    {
        return new self($raw);
    }

    /**
     * Transform raw Cubilis room list XML into domain PropertyListResponse.
     */
    public function toDomain(): PropertyListResponse
    {
        $roomStays = $this->raw->get('HotelRoomLists.0.HotelRoomList.0.RoomStays.0.RoomStay', []);
        $properties = collect($roomStays)
            ->flatMap(function ($stay) {
                $roomTypes = $stay['RoomTypes'][0]['RoomType'] ?? [];
                $ratePlans = $stay['RatePlans'][0]['RatePlan'] ?? [];

                return collect($roomTypes)
                    ->map(function ($roomType) {
                        $typeAttr = $roomType['@attributes'] ?? [];
                        $roomId = $typeAttr['RoomID'] ?? '';
                        $roomName = $typeAttr['RoomID'] ?? '';

                        return ['external_id' => $roomId, 'name' => $roomName];
                    })
                    ->map(fn ($props) => PropertyInfo::fromArray($props))
                    ->toArray();
            });

        return new PropertyListResponse(collect($properties));
    }
}

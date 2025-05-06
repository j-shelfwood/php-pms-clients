<?php

namespace Domain\Connections\Cubilis\Dtos;

class RoomTypeDto
{
    public function __construct(
        public readonly string $roomId,
        public readonly string $name,
        public readonly bool $isRoom
    ) {}

    public static function fromArray(array $data): self
    {
        $attrs = $data['@attributes'] ?? [];
        return new self(
            roomId: (string) ($attrs['RoomID'] ?? ''),
            name: (string) ($attrs['Name'] ?? ''),
            isRoom: isset($attrs['IsRoom']) ? filter_var($attrs['IsRoom'], FILTER_VALIDATE_BOOLEAN) : true
        );
    }
}

<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses;

use Carbon\Carbon; // Keep Carbon for date parsing
use Tightenco\Collect\Support\Collection; // Changed from Illuminate\Support\Collection

class PropertyImageResponse
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
        public readonly string $description,
        public readonly ?Carbon $modified
    ) {}

    public static function fromXml(Collection|array $data): self
    {
        $attributes = $data instanceof Collection ? $data->get('@attributes', []) : ($data['@attributes'] ?? []);
        if ($attributes instanceof Collection) { // Ensure attributes is an array for consistent access
            $attributes = $attributes->all();
        }

        $name = (string) ($attributes['name'] ?? ($data instanceof Collection ? $data->get('name') : ($data['name'] ?? 'default.jpg')));
        $url = (string) ($attributes['url'] ?? ($data instanceof Collection ? $data->get('url') : ($data['url'] ?? '')));

        $descriptionValue = $data instanceof Collection ? $data->get('#text') : ($data['#text'] ?? ($data[0] ?? ''));
        if (is_array($descriptionValue)) { // Handle cases where #text might be an array
            $descriptionValue = $descriptionValue[0] ?? '';
        }
        $description = (string) $descriptionValue;

        $modifiedDate = $attributes['modified'] ?? null;

        return new self(
            name: $name,
            url: $url,
            description: $description,
            modified: $modifiedDate ? Carbon::parse($modifiedDate) : null
        );
    }
}

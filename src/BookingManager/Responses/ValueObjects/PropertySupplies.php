<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects;

use Tightenco\Collect\Support\Collection;

class PropertySupplies
{
    public function __construct(
        public readonly bool $coffee,
        public readonly bool $tea,
        public readonly bool $milk,
        public readonly bool $sugar,
        public readonly bool $dishwasherTablets
    ) {}

    public static function fromXml(Collection|array $data): self
    {
        return new self(
            coffee: (bool) ($data instanceof Collection ? $data->get('coffee') : ($data['coffee'] ?? false)),
            tea: (bool) ($data instanceof Collection ? $data->get('tea') : ($data['tea'] ?? false)),
            milk: (bool) ($data instanceof Collection ? $data->get('milk') : ($data['milk'] ?? false)),
            sugar: (bool) ($data instanceof Collection ? $data->get('sugar') : ($data['sugar'] ?? false)),
            dishwasherTablets: (bool) ($data instanceof Collection ? $data->get('dishwasher_tablets') : ($data['dishwasher_tablets'] ?? false))
        );
    }
}

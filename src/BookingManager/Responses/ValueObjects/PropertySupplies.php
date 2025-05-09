<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;

class PropertySupplies
{
    public bool $coffee;
    public bool $tea;
    public bool $milk;
    public bool $sugar;
    public bool $dishwasherTablets;

    public function __construct(bool $coffee, bool $tea, bool $milk, bool $sugar, bool $dishwasherTablets)
    {
        $this->coffee = $coffee;
        $this->tea = $tea;
        $this->milk = $milk;
        $this->sugar = $sugar;
        $this->dishwasherTablets = $dishwasherTablets;
    }

    public static function fromXml(array $data): self
    {
        return new self(
            coffee: (bool) ($data['coffee'] ?? false),
            tea: (bool) ($data['tea'] ?? false),
            milk: (bool) ($data['milk'] ?? false),
            sugar: (bool) ($data['sugar'] ?? false),
            dishwasherTablets: (bool) ($data['dishwasher_tablets'] ?? false)
        );
    }
}

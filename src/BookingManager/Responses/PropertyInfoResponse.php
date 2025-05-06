<?php

namespace Shelfwood\PhpPms\Clients\BookingManager\Responses;

use Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects\PropertyContent;
use Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects\PropertyLocation;
use Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects\PropertyProvider;
use Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects\PropertyService;
use Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects\PropertySupplies;
use Shelfwood\PhpPms\Clients\BookingManager\Responses\ValueObjects\PropertyTax;
use Exception;
use Carbon\Carbon; // Keep Carbon
use Tightenco\Collect\Support\Collection; // Use Tightenco Collection
// Removed Log, Arr, PropertyInfoFactory, Arrayable

class PropertyInfoResponse
{
    public function __construct(
        public readonly int $external_id,
        public readonly string $name,
        public readonly ?string $identifier,
        public readonly string $status,
        public readonly array $property_types,
        public readonly PropertyProvider $provider,
        public readonly PropertyLocation $location,
        public readonly int $max_persons,
        public readonly int $minimal_nights,
        public readonly int $maximal_nights,
        public readonly ?Carbon $available_start,
        public readonly ?Carbon $available_end,
        public readonly int $floor,
        public readonly bool $stairs,
        public readonly ?float $size,
        public readonly int $bedrooms,
        public readonly int $single_bed,
        public readonly int $double_bed,
        public readonly int $single_sofa,
        public readonly int $double_sofa,
        public readonly int $single_bunk,
        public readonly int $bathrooms,
        public readonly int $toilets,
        public readonly bool $elevator,
        public readonly ?string $view,
        public readonly ?string $internet,
        public readonly ?string $internet_connection,
        public readonly ?string $parking,
        public readonly bool $airco,
        public readonly bool $fans,
        public readonly bool $balcony,
        public readonly bool $patio,
        public readonly bool $garden,
        public readonly bool $roof_terrace,
        public readonly ?string $tv,
        public readonly ?string $tv_connection,
        public readonly ?string $dvd,
        public readonly bool $computer,
        public readonly bool $printer,
        public readonly bool $iron,
        public readonly bool $dishwasher,
        public readonly bool $oven,
        public readonly bool $microwave,
        public readonly bool $grill,
        public readonly bool $hob,
        public readonly bool $fridge,
        public readonly bool $freezer,
        public readonly bool $washingmachine,
        public readonly bool $dryer,
        public readonly bool $toaster,
        public readonly bool $kettle,
        public readonly bool $coffeemachine,
        public readonly int $bathtub,
        public readonly int $jacuzzi,
        public readonly int $shower_regular,
        public readonly int $shower_steam,
        public readonly ?string $swimmingpool,
        public readonly ?string $sauna,
        public readonly bool $hairdryer,
        public readonly bool $entresol,
        public readonly bool $wheelchair_friendly,
        public readonly bool $smoking_allowed,
        public readonly bool $pets_allowed,
        public readonly bool $heating,
        public readonly PropertySupplies $supplies,
        public readonly PropertyService $service,
        public readonly float $cleaning_costs,
        public readonly float $deposit_costs,
        public readonly ?string $check_in,
        public readonly ?string $check_out,
        public readonly PropertyTax $tax,
        public readonly ?float $prepayment,
        public readonly ?float $fee,
        public readonly PropertyContent $content,
        /** @var Collection<int, PropertyImageResponse> */
        public readonly Collection $images,
        public readonly ?Carbon $external_created_at,
        public readonly ?Carbon $external_updated_at
    ) {}

    // Removed factory() method
    // Removed toDatabase() method

    public static function map(Collection|array $data): self
    {
        $propertyData = $data;
        // Handle potential nesting under 'properties' -> 'property' or just 'property'
        if ($data instanceof Collection) {
            if (! $data->has('@attributes') && $data->has('properties.property')) {
                $propertyData = collect($data->get('properties.property'));
            } elseif (! $data->has('@attributes') && $data->has('property')) {
                $propertyData = collect($data->get('property'));
            }
        } elseif (is_array($data)) {
            if (! isset($data['@attributes']) && isset($data['properties']['property'])) {
                $propertyData = collect($data['properties']['property']);
            } elseif (! isset($data['@attributes']) && isset($data['property'])) {
                $propertyData = collect($data['property']);
            }
        }

        if (($propertyData instanceof Collection && $propertyData->isEmpty()) || (is_array($propertyData) && empty($propertyData))) {
            // Removed Log call
            throw new Exception('Invalid response structure: Missing property data.');
        }

        $attributes = $propertyData instanceof Collection ? $propertyData->get('@attributes', []) : ($propertyData['@attributes'] ?? []);
        if ($attributes instanceof Collection) $attributes = $attributes->all(); // Ensure array

        $getInt = function ($key, $default = 0) use ($propertyData) {
            $value = $propertyData instanceof Collection ? $propertyData->get($key) : ($propertyData[$key] ?? null);
            return (int) ($value ?? $default);
        };
        $getFloat = function ($key, $default = 0.0) use ($propertyData) {
            $value = $propertyData instanceof Collection ? $propertyData->get($key) : ($propertyData[$key] ?? null);
            return (float) ($value ?? $default);
        };
        $getBool = function ($key, $default = false) use ($propertyData) {
            $value = $propertyData instanceof Collection ? $propertyData->get($key) : ($propertyData[$key] ?? null);
            return (bool) ($value ?? $default);
        };
        $getString = function ($key, $default = null) use ($propertyData) {
            $value = $propertyData instanceof Collection ? $propertyData->get($key) : ($propertyData[$key] ?? null);
            if ($value === false) return null;
            return ($value !== null && !is_array($value)) ? (string) $value : $default;
        };
        $getDate = function ($key) use ($propertyData) {
            $dateStr = $propertyData instanceof Collection ? $propertyData->get($key) : ($propertyData[$key] ?? null);
            try {
                return $dateStr ? Carbon::parse($dateStr) : null;
            } catch (\Throwable $e) {
                // Removed Log call, consider a different way to handle parse errors if necessary
                return null;
            }
        };

        $typesString = $getString('type', '');
        $propertyTypes = !empty($typesString) ? explode(',', $typesString) : [];

        $imagesData = $propertyData instanceof Collection ? $propertyData->get('images.image', []) : ($propertyData['images']['image'] ?? []);
        if ($imagesData instanceof Collection) $imagesData = $imagesData->all();

        if (!empty($imagesData) && isset($imagesData['name'])) { // Check if it's a single image not in an array
            $imagesData = [$imagesData];
        }

        $imagesCollection = new Collection();
        if (is_array($imagesData)) {
            foreach ($imagesData as $imageData) {
                if (!empty($imageData)) {
                    // Corrected: PropertyImageResponse::fromXml, not map
                    $imagesCollection->push(PropertyImageResponse::fromXml(collect($imageData)));
                }
            }
        }

        return new self(
            external_id: (int) ($attributes['id'] ?? 0),
            name: (string) ($attributes['name'] ?? ''),
            identifier: $getString('@attributes.identifier'),
            status: (string) ($attributes['status'] ?? ''),
            property_types: $propertyTypes,
            provider: PropertyProvider::fromXml(collect($propertyData instanceof Collection ? $propertyData->get('provider', []) : ($propertyData['provider'] ?? []))),
            location: PropertyLocation::fromXml(collect($propertyData instanceof Collection ? $propertyData->get('location', []) : ($propertyData['location'] ?? []))),
            max_persons: $getInt('max_persons'),
            minimal_nights: $getInt('minimal_nights'),
            maximal_nights: $getInt('maximal_nights'),
            available_start: $getDate('available_start'),
            available_end: $getDate('available_end'),
            floor: $getInt('floor'),
            stairs: $getBool('stairs'),
            size: $getFloat('size'),
            bedrooms: $getInt('bedrooms'),
            single_bed: $getInt('single_bed'),
            double_bed: $getInt('double_bed'),
            single_sofa: $getInt('single_sofa'),
            double_sofa: $getInt('double_sofa'),
            single_bunk: $getInt('single_bunk'),
            bathrooms: $getInt('bathrooms'),
            toilets: $getInt('toilets'),
            elevator: $getBool('elevator'),
            view: $getString('view'),
            internet: $getString('internet'),
            internet_connection: $getString('internet_connection'),
            parking: $getString('parking'),
            airco: $getBool('airco'),
            fans: $getBool('fans'),
            balcony: $getBool('balcony'),
            patio: $getBool('patio'),
            garden: $getBool('garden'),
            roof_terrace: $getBool('roof_terrace'),
            tv: $getString('tv'),
            tv_connection: $getString('tv_connection'),
            dvd: $getString('dvd'),
            computer: $getBool('computer'),
            printer: $getBool('printer'),
            iron: $getBool('iron'),
            dishwasher: $getBool('dishwasher'),
            oven: $getBool('oven'),
            microwave: $getBool('microwave'),
            grill: $getBool('grill'),
            hob: $getBool('hob'),
            fridge: $getBool('fridge'),
            freezer: $getBool('freezer'),
            washingmachine: $getBool('washingmachine'),
            dryer: $getBool('dryer'),
            toaster: $getBool('toaster'),
            kettle: $getBool('kettle'),
            coffeemachine: $getBool('coffeemachine'),
            bathtub: $getInt('bathtub'),
            jacuzzi: $getInt('jacuzzi'),
            shower_regular: $getInt('shower_regular'),
            shower_steam: $getInt('shower_steam'),
            swimmingpool: $getString('swimmingpool'),
            sauna: $getString('sauna'),
            hairdryer: $getBool('hairdryer'),
            entresol: $getBool('entresol'),
            wheelchair_friendly: $getBool('wheelchair_friendly'),
            smoking_allowed: $getBool('smoking_allowed'),
            pets_allowed: $getBool('pets_allowed'),
            heating: $getBool('heating'),
            supplies: PropertySupplies::fromXml(collect($propertyData instanceof Collection ? $propertyData->get('supplies', []) : ($propertyData['supplies'] ?? []))),
            service: PropertyService::fromXml(collect($propertyData instanceof Collection ? $propertyData->get('service', []) : ($propertyData['service'] ?? []))),
            cleaning_costs: $getFloat('cleaning_costs'),
            deposit_costs: $getFloat('deposit_costs'),
            check_in: $getString('check_in'),
            check_out: $getString('check_out'),
            tax: PropertyTax::fromXml(collect($propertyData instanceof Collection ? $propertyData->get('tax', []) : ($propertyData['tax'] ?? []))),
            prepayment: $getFloat('prepayment'),
            fee: $getFloat('fee'),
            content: PropertyContent::fromXml(collect($propertyData instanceof Collection ? $propertyData->get('content', []) : ($propertyData['content'] ?? []))),
            images: $imagesCollection,
            external_created_at: $getDate('external_created_at'),
            external_updated_at: $getDate('external_updated_at')
        );
    }
}

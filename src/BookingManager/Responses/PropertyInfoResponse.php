<?php

namespace Domain\Connections\BookingManager\Responses;

use Database\Factories\PropertyInfoFactory;
use Domain\Connections\BookingManager\Responses\ValueObjects\PropertyContent;
use Domain\Connections\BookingManager\Responses\ValueObjects\PropertyLocation;
use Domain\Connections\BookingManager\Responses\ValueObjects\PropertyProvider;
use Domain\Connections\BookingManager\Responses\ValueObjects\PropertyService;
use Domain\Connections\BookingManager\Responses\ValueObjects\PropertySupplies;
use Domain\Connections\BookingManager\Responses\ValueObjects\PropertyTax;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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
        public readonly bool $heating, // Added from mock
        public readonly PropertySupplies $supplies,
        public readonly PropertyService $service,
        public readonly float $cleaning_costs,
        public readonly float $deposit_costs,
        public readonly ?string $check_in, // Added from mock
        public readonly ?string $check_out, // Added from mock
        public readonly PropertyTax $tax,
        public readonly ?float $prepayment, // Changed to float, added from mock
        public readonly ?float $fee, // Changed to float, added from mock
        public readonly PropertyContent $content,
        /** @var Collection<int, PropertyImageResponse> */
        public readonly Collection $images,
        public readonly ?Carbon $external_created_at,
        public readonly ?Carbon $external_updated_at
    ) {}

    public static function factory(): PropertyInfoFactory
    {
        return new PropertyInfoFactory;
    }

    public function toDatabase(): array
    {
        $array = $this->toArray();
        $array['provider_id'] = $this->provider->id;
        $array['provider_code'] = $this->provider->code;
        $array['provider_name'] = $this->provider->name;
        $array['latitude'] = $this->location->latitude;
        $array['longitude'] = $this->location->longitude;
        $array['street'] = $this->location->address;
        $array['zipcode'] = $this->location->zipcode;
        $array['city'] = $this->location->city;
        $array['country'] = $this->location->country;
        $array['area'] = $this->location->area;
        unset($array['provider'], $array['location'], $array['tax'], $array['content'], $array['supplies'], $array['service'], $array['images']);

        $array['available_start'] = $this->available_start?->toDateString();
        $array['available_end'] = $this->available_end?->toDateString();
        $array['external_created_at'] = $this->external_created_at?->toDateTimeString();
        $array['external_updated_at'] = $this->external_updated_at?->toDateTimeString();
        $array['property_types'] = implode(',', $this->property_types); // Store as comma-separated string

        return $array;
    }

    public static function map(Collection $data): self
    {
        // Handle potential nesting under 'properties' -> 'property'
        $propertyData = $data;
        if (! $data->has('@attributes') && $data->has('properties.property')) {
            $propertyData = collect($data->get('properties.property'));
        } elseif (! $data->has('@attributes') && $data->has('property')) {
            // Handle case where it might be directly under 'property' without 'properties' wrapper
            $propertyData = collect($data->get('property'));
        }

        if ($propertyData->isEmpty()) {
            Log::channel('sync')->error('PropertyInfoResponse::map - Property data is empty or not found', ['input_data' => $data]);
            throw new Exception('Invalid response structure: Missing property data.');
        }

        $attributes = $propertyData->get('@attributes', []);
        $getInt = fn ($key, $default = 0) => (int) Arr::get($propertyData, $key, $default);
        $getFloat = fn ($key, $default = 0.0) => (float) Arr::get($propertyData, $key, $default);
        $getBool = fn ($key, $default = false) => (bool) Arr::get($propertyData, $key, $default);
        $getString = fn ($key, $default = null) => (
            Arr::get($propertyData, $key) === false ? null : (
                Arr::get($propertyData, $key) !== null && ! is_array(Arr::get($propertyData, $key))
                    ? (string) Arr::get($propertyData, $key)
                    : $default
            )
        );
        $getDate = function ($key) use ($propertyData) {
            $dateStr = Arr::get($propertyData, $key);
            try {
                return $dateStr ? Carbon::parse($dateStr) : null;
            } catch (\Throwable $e) {
                Log::channel('sync')->warning("Failed to parse date for key '{$key}'", ['value' => $dateStr, 'error' => $e->getMessage()]);

                return null;
            }
        };

        // Parse comma-separated property types
        $typesString = $getString('type', '');
        $propertyTypes = ! empty($typesString) ? explode(',', $typesString) : [];

        return new self(
            external_id: (int) ($attributes['id'] ?? 0),
            name: (string) ($attributes['name'] ?? ''),
            identifier: $getString('@attributes.identifier'), // Get identifier attribute
            status: (string) ($attributes['status'] ?? ''),
            property_types: $propertyTypes,
            provider: PropertyProvider::fromXml($propertyData->get('provider', [])),
            location: PropertyLocation::fromXml($propertyData->get('location', [])),
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
            supplies: PropertySupplies::fromXml($propertyData->get('supplies', [])),
            service: PropertyService::fromXml($propertyData->get('service', [])),
            cleaning_costs: $getFloat('cleaning_costs'),
            deposit_costs: $getFloat('deposit_costs'),
            check_in: $getString('check_in'),
            check_out: $getString('check_out'),
            tax: PropertyTax::fromXml($propertyData->get('tax', [])),
            prepayment: $getFloat('prepayment'),
            fee: $getFloat('fee'),
            content: PropertyContent::fromXml($propertyData->get('content', [])),
            images: self::createImages($propertyData->get('images.image', [])),
            external_created_at: $getDate('created'),
            external_updated_at: $getDate('modified')
        );
    }

    private static function createImages(array|Collection $imagesData): Collection
    {
        // Ensure $imagesData is always treated as a list of items
        if (! is_array($imagesData) || (Arr::isAssoc($imagesData) && ! empty($imagesData))) {
            $imagesData = [$imagesData]; // Wrap single assoc array or non-array
        } elseif (empty($imagesData)) {
            return collect(); // Return empty collection if no images
        }

        return collect($imagesData)
            ->map(function ($imageData, $index) {
                try {
                    if (empty($imageData) || ! is_array($imageData)) {
                        Log::channel('sync')->warning('Skipping invalid image data entry', ['index' => $index, 'data' => $imageData]);

                        return null;
                    }

                    return PropertyImageResponse::fromXml($imageData);
                } catch (\Exception $e) {
                    Log::channel('sync')->warning('Failed to create PropertyImage', [
                        'error' => $e->getMessage(),
                        'image_data' => $imageData,
                        'index' => $index,
                    ]);

                    return null;
                }
            })
            ->filter(); // Remove nulls from failed mappings
    }

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        // Convert object properties to array, handling nested DTOs and collections
        $array = [];
        foreach (get_object_vars($this) as $key => $value) {
            if ($value instanceof Carbon) {
                $array[$key] = $value->toIso8601String();
            } elseif ($value instanceof Collection) {
                $array[$key] = $value->map(fn ($item) => $item instanceof Arrayable ? $item->toArray() : $item)->toArray();
            } elseif (is_object($value) && method_exists($value, 'toArray')) {
                $array[$key] = $value->toArray(); // Assuming nested DTOs have toArray
            } elseif (is_object($value)) {
                // Basic DTOs might not need toArray, just get vars
                $array[$key] = get_object_vars($value);
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }
}

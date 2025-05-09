<?php

namespace Shelfwood\PhpPms\BookingManager\Responses\ValueObjects;

use Exception;
use Carbon\Carbon;

class PropertyDetails
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
        /** @var PropertyImageResponse[] */
        public readonly array $images,
        public readonly ?Carbon $external_created_at,
        public readonly ?Carbon $external_updated_at
    ) {}

    public static function map(array $data): self
    {

        $propertyData = $data;
        if (isset($data['properties']['property'])) {
            $propertyData = $data['properties']['property'];
        } elseif (isset($data['property'])) {
            $propertyData = $data['property'];
        }

        if (empty($propertyData)) {
            throw new Exception('Invalid response structure: Missing property data.');
        }

        $attributes = $propertyData['@attributes'] ?? [];
        $id = (int) ($attributes['id'] ?? 0);
        $name = (string) ($attributes['name'] ?? '');
        $status = (string) ($attributes['status'] ?? '');
        $identifier = isset($attributes['identifier']) ? (string) $attributes['identifier'] : null;

        $getString = function($key, $default = null) use ($propertyData) {
            $value = $propertyData[$key] ?? $default;
            if (is_array($value) && empty($value)) {
                return $default;
            }
            if (!is_scalar($value) && $value !== null) {
                return $default;
            }
            return $value === null ? null : (string) $value;
        };

        $getInt = function($key, $default = 0) use ($propertyData) {
            $value = $propertyData[$key] ?? $default;
            return is_numeric($value) ? (int) $value : $default;
        };

        $getFloat = function($key, $default = 0.0) use ($propertyData) {
            $value = $propertyData[$key] ?? $default;
            return is_numeric($value) ? (float) $value : $default;
        };

        $getBool = function($key, $default = false) use ($propertyData) {
            $value = $propertyData[$key] ?? $default;
            return is_bool($value) ? $value : (bool) $default;
        };

        $getDate = function($key) use ($propertyData) {
            $value = $propertyData[$key] ?? null;
            return ($value !== null && !empty($value) && !is_array($value)) ? Carbon::parse((string) $value) : null;
        };

        $typesString = $getString('type', '');
        $propertyTypes = !empty($typesString) ? explode(',', $typesString) : [];


        $imagesData = $propertyData['images']['image'] ?? [];
        if (!is_array($imagesData)) {
            $imagesData = [];
        } elseif (isset($imagesData['@attributes']) || isset($imagesData['name'])) {
            $imagesData = [$imagesData];
        }
        $imagesData = array_filter($imagesData, function ($img) {
            return is_array($img) && isset($img['@attributes']);
        });

        return new self(
            external_id: $id,
            name: $name,
            identifier: $identifier,
            status: $status,
            property_types: $propertyTypes,
            provider: PropertyProvider::fromXml($propertyData['provider'] ?? []),
            location: PropertyLocation::fromXml($propertyData['location'] ?? []),
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
            supplies: PropertySupplies::fromXml($propertyData['supplies'] ?? []),
            service: PropertyService::fromXml($propertyData['service'] ?? []),
            cleaning_costs: $getFloat('cleaning_costs'),
            deposit_costs: $getFloat('deposit_costs'),
            check_in: $getString('check_in'),
            check_out: $getString('check_out'),
            tax: PropertyTax::fromXml($propertyData['tax'] ?? []),
            prepayment: $getFloat('prepayment'),
            fee: $getFloat('fee'),
            content: PropertyContent::fromXml($propertyData['content'] ?? []),
            images: array_map(fn($imageData) => PropertyImage::fromXml($imageData), $imagesData),
            external_created_at: $getDate('external_created_at'),
            external_updated_at: $getDate('external_updated_at')
        );
    }
}

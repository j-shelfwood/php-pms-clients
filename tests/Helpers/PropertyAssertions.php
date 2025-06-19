<?php

namespace Tests\Helpers;

use Carbon\Carbon;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyDetails;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyLocation;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyProvider;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertySupplies;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyService;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyTax;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyContent;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyImage;
use Shelfwood\PhpPms\BookingManager\Enums\PropertyStatus;
use Shelfwood\PhpPms\BookingManager\Enums\ViewType;
use Shelfwood\PhpPms\BookingManager\Enums\InternetType;
use Shelfwood\PhpPms\BookingManager\Enums\InternetConnectionType;
use Shelfwood\PhpPms\BookingManager\Enums\ParkingType;
use Shelfwood\PhpPms\BookingManager\Enums\SwimmingPoolType;
use Shelfwood\PhpPms\BookingManager\Enums\SaunaType;
use Shelfwood\PhpPms\BookingManager\Enums\TvType;
use Shelfwood\PhpPms\BookingManager\Enums\TvConnectionType;
use Shelfwood\PhpPms\BookingManager\Enums\DvdType;
use Shelfwood\PhpPms\BookingManager\Enums\TaxType;

/**
 * Universal property details assertion that takes expected data array
 */
function assertPropertyDetails(PropertyDetails $actualProperty, array $expected): void
{
    // Basic property information
    expect($actualProperty->external_id)->toBe($expected['external_id']);
    expect($actualProperty->name)->toBe($expected['name']);
    expect($actualProperty->identifier)->toBe($expected['identifier']);
    expect($actualProperty->status?->value)->toBe($expected['status']);
    expect($actualProperty->property_types)->toBe($expected['property_types']);

    // Provider information
    expect($actualProperty->provider->id)->toBe($expected['provider']['id']);
    expect($actualProperty->provider->code)->toBe($expected['provider']['code']);
    expect($actualProperty->provider->name)->toBe($expected['provider']['name']);

    // Location information
    expect($actualProperty->location->latitude)->toBe($expected['location']['latitude']);
    expect($actualProperty->location->longitude)->toBe($expected['location']['longitude']);
    expect($actualProperty->location->address)->toBe($expected['location']['address']);
    expect($actualProperty->location->zipcode)->toBe($expected['location']['zipcode']);
    expect($actualProperty->location->city)->toBe($expected['location']['city']);
    expect($actualProperty->location->country)->toBe($expected['location']['country']);
    expect($actualProperty->location->cityLatitude)->toBe($expected['location']['cityLatitude']);
    expect($actualProperty->location->cityLongitude)->toBe($expected['location']['cityLongitude']);
    expect($actualProperty->location->area)->toBe($expected['location']['area']);

    // Property details
    expect($actualProperty->max_persons)->toBe($expected['max_persons']);
    expect($actualProperty->minimal_nights)->toBe($expected['minimal_nights']);
    expect($actualProperty->maximal_nights)->toBe($expected['maximal_nights']);
    expect($actualProperty->available_start?->toDateString())->toBe($expected['available_start']);
    expect($actualProperty->available_end?->toDateString())->toBe($expected['available_end']);
    expect($actualProperty->floor)->toBe($expected['floor']);
    expect($actualProperty->stairs)->toBe($expected['stairs']);
    expect($actualProperty->size)->toBe($expected['size']);

    // Bedroom and bed information
    expect($actualProperty->bedrooms)->toBe($expected['bedrooms']);
    expect($actualProperty->single_bed)->toBe($expected['single_bed']);
    expect($actualProperty->double_bed)->toBe($expected['double_bed']);
    expect($actualProperty->single_sofa)->toBe($expected['single_sofa']);
    expect($actualProperty->double_sofa)->toBe($expected['double_sofa']);
    expect($actualProperty->single_bunk)->toBe($expected['single_bunk']);
    expect($actualProperty->bathrooms)->toBe($expected['bathrooms']);
    expect($actualProperty->toilets)->toBe($expected['toilets']);

    // Accessibility and views
    expect($actualProperty->elevator)->toBe($expected['elevator']);
    expect($actualProperty->view?->value)->toBe($expected['view']);

    // Technology and connectivity
    expect($actualProperty->internet?->value)->toBe($expected['internet']);
    expect($actualProperty->internet_connection?->value)->toBe($expected['internet_connection']);
    expect($actualProperty->parking?->value)->toBe($expected['parking']);
    expect($actualProperty->tv?->value)->toBe($expected['tv']);
    expect($actualProperty->tv_connection?->value)->toBe($expected['tv_connection']);
    expect($actualProperty->dvd?->value)->toBe($expected['dvd']);

    // Climate and outdoor features
    expect($actualProperty->airco)->toBe($expected['airco']);
    expect($actualProperty->fans)->toBe($expected['fans']);
    expect($actualProperty->balcony)->toBe($expected['balcony']);
    expect($actualProperty->patio)->toBe($expected['patio']);
    expect($actualProperty->garden)->toBe($expected['garden']);
    expect($actualProperty->roof_terrace)->toBe($expected['roof_terrace']);

    // Equipment and appliances
    expect($actualProperty->computer)->toBe($expected['computer']);
    expect($actualProperty->printer)->toBe($expected['printer']);
    expect($actualProperty->iron)->toBe($expected['iron']);
    expect($actualProperty->dishwasher)->toBe($expected['dishwasher']);
    expect($actualProperty->oven)->toBe($expected['oven']);
    expect($actualProperty->microwave)->toBe($expected['microwave']);
    expect($actualProperty->grill)->toBe($expected['grill']);
    expect($actualProperty->hob)->toBe($expected['hob']);
    expect($actualProperty->fridge)->toBe($expected['fridge']);
    expect($actualProperty->freezer)->toBe($expected['freezer']);
    expect($actualProperty->washingmachine)->toBe($expected['washingmachine']);
    expect($actualProperty->dryer)->toBe($expected['dryer']);
    expect($actualProperty->toaster)->toBe($expected['toaster']);
    expect($actualProperty->kettle)->toBe($expected['kettle']);
    expect($actualProperty->coffeemachine)->toBe($expected['coffeemachine']);

    // Bathroom and spa features
    expect($actualProperty->bathtub)->toBe($expected['bathtub']);
    expect($actualProperty->jacuzzi)->toBe($expected['jacuzzi']);
    expect($actualProperty->shower_regular)->toBe($expected['shower_regular']);
    expect($actualProperty->shower_steam)->toBe($expected['shower_steam']);
    expect($actualProperty->swimmingpool?->value)->toBe($expected['swimmingpool']);
    expect($actualProperty->sauna?->value)->toBe($expected['sauna']);
    expect($actualProperty->hairdryer)->toBe($expected['hairdryer']);

    // Property policies and features
    expect($actualProperty->entresol)->toBe($expected['entresol']);
    expect($actualProperty->wheelchair_friendly)->toBe($expected['wheelchair_friendly']);
    expect($actualProperty->smoking_allowed)->toBe($expected['smoking_allowed']);
    expect($actualProperty->pets_allowed)->toBe($expected['pets_allowed']);
    expect($actualProperty->heating)->toBe($expected['heating']);

    // Supplies
    expect($actualProperty->supplies->coffee)->toBe($expected['supplies']['coffee']);
    expect($actualProperty->supplies->tea)->toBe($expected['supplies']['tea']);
    expect($actualProperty->supplies->milk)->toBe($expected['supplies']['milk']);
    expect($actualProperty->supplies->sugar)->toBe($expected['supplies']['sugar']);
    expect($actualProperty->supplies->dishwasherTablets)->toBe($expected['supplies']['dishwasherTablets']);

    // Services
    expect($actualProperty->service->linen)->toBe($expected['service']['linen']);
    expect($actualProperty->service->towels)->toBe($expected['service']['towels']);
    expect($actualProperty->service->cleaning)->toBe($expected['service']['cleaning']);

    // Financial information
    expect($actualProperty->cleaning_costs)->toBe($expected['cleaning_costs']);
    expect($actualProperty->deposit_costs)->toBe($expected['deposit_costs']);
    expect($actualProperty->check_in)->toBe($expected['check_in']);
    expect($actualProperty->check_out)->toBe($expected['check_out']);

    // Tax information
    expect($actualProperty->tax->vat)->toBe($expected['tax']['vat']);
    expect($actualProperty->tax->other)->toBe($expected['tax']['other']);
    expect($actualProperty->tax->otherType?->value)->toBe($expected['tax']['otherType']);

    expect($actualProperty->prepayment)->toBe($expected['prepayment']);
    expect($actualProperty->fee)->toBe($expected['fee']);

    // Content information
    expect($actualProperty->content->short)->toBe($expected['content']['short']);
    expect($actualProperty->content->full)->toBe($expected['content']['full']);
    expect($actualProperty->content->area)->toBe($expected['content']['area']);
    expect($actualProperty->content->arrival)->toBe($expected['content']['arrival']);
    expect($actualProperty->content->termsAndConditions)->toBe($expected['content']['termsAndConditions']);

    // Images - use dedicated assertion function for comprehensive validation
    assertPropertyImagesMatchExpected($actualProperty->images, $expected['images']);

    // External timestamps
    expect($actualProperty->external_created_at?->toISOString())->toBe($expected['external_created_at']);
    expect($actualProperty->external_updated_at?->toISOString())->toBe($expected['external_updated_at']);
}

function assertPropertyDetailsMatchesExpected(PropertyDetails $actualProperty): void
{
    $expected = TestData::getExpectedPropertyData();

    // Basic property information
    expect($actualProperty->external_id)->toBe($expected['external_id']);
    expect($actualProperty->name)->toBe($expected['name']);
    expect($actualProperty->identifier)->toBe($expected['identifier']);
    expect($actualProperty->status)->toBeInstanceOf(PropertyStatus::class);
    expect($actualProperty->status->value)->toBe($expected['status']);
    expect($actualProperty->property_types)->toBe($expected['property_types']);

    // Capacity and availability
    expect($actualProperty->max_persons)->toBe($expected['max_persons']);
    expect($actualProperty->minimal_nights)->toBe($expected['minimal_nights']);
    expect($actualProperty->maximal_nights)->toBe($expected['maximal_nights']);

    // Dates (Carbon objects)
    expect($actualProperty->available_start)->toBeInstanceOf(Carbon::class);
    expect($actualProperty->available_start->format('Y-m-d'))->toBe($expected['available_start']);
    expect($actualProperty->available_end)->toBeInstanceOf(Carbon::class);
    expect($actualProperty->available_end->format('Y-m-d'))->toBe($expected['available_end']);

    // Physical characteristics
    expect($actualProperty->floor)->toBe($expected['floor']);
    expect($actualProperty->stairs)->toBe($expected['stairs']);
    expect($actualProperty->size)->toBe($expected['size']);
    expect($actualProperty->bedrooms)->toBe($expected['bedrooms']);
    expect($actualProperty->single_bed)->toBe($expected['single_bed']);
    expect($actualProperty->double_bed)->toBe($expected['double_bed']);
    expect($actualProperty->single_sofa)->toBe($expected['single_sofa']);
    expect($actualProperty->double_sofa)->toBe($expected['double_sofa']);
    expect($actualProperty->single_bunk)->toBe($expected['single_bunk']);
    expect($actualProperty->bathrooms)->toBe($expected['bathrooms']);
    expect($actualProperty->toilets)->toBe($expected['toilets']);
    expect($actualProperty->elevator)->toBe($expected['elevator']);

    // Enums
    expect($actualProperty->view)->toBeInstanceOf(ViewType::class);
    expect($actualProperty->view->value)->toBe($expected['view']);
    expect($actualProperty->internet)->toBeInstanceOf(InternetType::class);
    expect($actualProperty->internet->value)->toBe($expected['internet']);
    expect($actualProperty->internet_connection)->toBeInstanceOf(InternetConnectionType::class);
    expect($actualProperty->internet_connection->value)->toBe($expected['internet_connection']);
    expect($actualProperty->parking)->toBeInstanceOf(ParkingType::class);
    expect($actualProperty->parking->value)->toBe($expected['parking']);

    // Amenities (booleans)
    expect($actualProperty->airco)->toBe($expected['airco']);
    expect($actualProperty->fans)->toBe($expected['fans']);
    expect($actualProperty->balcony)->toBe($expected['balcony']);
    expect($actualProperty->patio)->toBe($expected['patio']);
    expect($actualProperty->garden)->toBe($expected['garden']);
    expect($actualProperty->roof_terrace)->toBe($expected['roof_terrace']);

    // Electronics
    expect($actualProperty->tv)->toBeInstanceOf(TvType::class);
    expect($actualProperty->tv->value)->toBe($expected['tv']);
    expect($actualProperty->tv_connection)->toBeInstanceOf(TvConnectionType::class);
    expect($actualProperty->tv_connection->value)->toBe($expected['tv_connection']);
    expect($actualProperty->dvd)->toBeInstanceOf(DvdType::class);
    expect($actualProperty->dvd->value)->toBe($expected['dvd']);
    expect($actualProperty->computer)->toBe($expected['computer']);
    expect($actualProperty->printer)->toBe($expected['printer']);

    // Kitchen appliances
    expect($actualProperty->iron)->toBe($expected['iron']);
    expect($actualProperty->dishwasher)->toBe($expected['dishwasher']);
    expect($actualProperty->oven)->toBe($expected['oven']);
    expect($actualProperty->microwave)->toBe($expected['microwave']);
    expect($actualProperty->grill)->toBe($expected['grill']);
    expect($actualProperty->hob)->toBe($expected['hob']);
    expect($actualProperty->fridge)->toBe($expected['fridge']);
    expect($actualProperty->freezer)->toBe($expected['freezer']);
    expect($actualProperty->washingmachine)->toBe($expected['washingmachine']);
    expect($actualProperty->dryer)->toBe($expected['dryer']);
    expect($actualProperty->toaster)->toBe($expected['toaster']);
    expect($actualProperty->kettle)->toBe($expected['kettle']);
    expect($actualProperty->coffeemachine)->toBe($expected['coffeemachine']);

    // Bathroom facilities (integers)
    expect($actualProperty->bathtub)->toBe($expected['bathtub']);
    expect($actualProperty->jacuzzi)->toBe($expected['jacuzzi']);
    expect($actualProperty->shower_regular)->toBe($expected['shower_regular']);
    expect($actualProperty->shower_steam)->toBe($expected['shower_steam']);

    // Wellness
    expect($actualProperty->swimmingpool)->toBeInstanceOf(SwimmingPoolType::class);
    expect($actualProperty->swimmingpool->value)->toBe($expected['swimmingpool']);
    expect($actualProperty->sauna)->toBeInstanceOf(SaunaType::class);
    expect($actualProperty->sauna->value)->toBe($expected['sauna']);
    expect($actualProperty->hairdryer)->toBe($expected['hairdryer']);

    // Special characteristics
    expect($actualProperty->entresol)->toBe($expected['entresol']);
    expect($actualProperty->wheelchair_friendly)->toBe($expected['wheelchair_friendly']);
    expect($actualProperty->smoking_allowed)->toBe($expected['smoking_allowed']);
    expect($actualProperty->pets_allowed)->toBe($expected['pets_allowed']);
    expect($actualProperty->heating)->toBe($expected['heating']);

    // Financial
    expect($actualProperty->cleaning_costs)->toBe($expected['cleaning_costs']);
    expect($actualProperty->deposit_costs)->toBe($expected['deposit_costs']);
    expect($actualProperty->prepayment)->toBe($expected['prepayment']);
    expect($actualProperty->fee)->toBe($expected['fee']);

    // Times
    expect($actualProperty->check_in)->toBe($expected['check_in']);
    expect($actualProperty->check_out)->toBe($expected['check_out']);

    // External timestamps
    expect($actualProperty->external_created_at)->toBeInstanceOf(Carbon::class);
    expect($actualProperty->external_created_at->toISOString())->toBe($expected['external_created_at']);
    expect($actualProperty->external_updated_at)->toBeInstanceOf(Carbon::class);
    expect($actualProperty->external_updated_at->toISOString())->toBe($expected['external_updated_at']);

    // Nested objects - call specific validators
    assertPropertyProviderMatchesExpected($actualProperty->provider, $expected['provider']);
    assertPropertyLocationMatchesExpected($actualProperty->location, $expected['location']);
    assertPropertySuppliesMatchesExpected($actualProperty->supplies, $expected['supplies']);
    assertPropertyServiceMatchesExpected($actualProperty->service, $expected['service']);
    assertPropertyTaxMatchesExpected($actualProperty->tax, $expected['tax']);
    assertPropertyContentMatchesExpected($actualProperty->content, $expected['content']);
    assertPropertyImagesMatchExpected($actualProperty->images, $expected['images']);
}

function assertPropertyProviderMatchesExpected(PropertyProvider $provider, array $expected): void
{
    expect($provider->id)->toBe($expected['id']);
    expect($provider->code)->toBe($expected['code']);
    expect($provider->name)->toBe($expected['name']);
}

function assertPropertyLocationMatchesExpected(PropertyLocation $location, array $expected): void
{
    expect($location->latitude)->toBe($expected['latitude']);
    expect($location->longitude)->toBe($expected['longitude']);
    expect($location->address)->toBe($expected['address']);
    expect($location->zipcode)->toBe($expected['zipcode']);
    expect($location->city)->toBe($expected['city']);
    expect($location->country)->toBe($expected['country']);
    expect($location->cityLatitude)->toBe($expected['cityLatitude']);
    expect($location->cityLongitude)->toBe($expected['cityLongitude']);
    expect($location->area)->toBe($expected['area']);
}

function assertPropertySuppliesMatchesExpected(PropertySupplies $supplies, array $expected): void
{
    expect($supplies->coffee)->toBe($expected['coffee']);
    expect($supplies->tea)->toBe($expected['tea']);
    expect($supplies->milk)->toBe($expected['milk']);
    expect($supplies->sugar)->toBe($expected['sugar']);
    expect($supplies->dishwasherTablets)->toBe($expected['dishwasherTablets']);
}

function assertPropertyServiceMatchesExpected(PropertyService $service, array $expected): void
{
    expect($service->linen)->toBe($expected['linen']);
    expect($service->towels)->toBe($expected['towels']);
    expect($service->cleaning)->toBe($expected['cleaning']);
}

function assertPropertyTaxMatchesExpected(PropertyTax $tax, array $expected): void
{
    expect($tax->vat)->toBe($expected['vat']);
    expect($tax->other)->toBe($expected['other']);
    expect($tax->otherType)->toBeInstanceOf(TaxType::class);
    expect($tax->otherType->value)->toBe($expected['otherType']);
}

function assertPropertyContentMatchesExpected(PropertyContent $content, array $expected): void
{
    expect($content->short)->toBe($expected['short']);
    expect($content->full)->toBe($expected['full']);
    expect($content->area)->toBe($expected['area']);
    expect($content->arrival)->toBe($expected['arrival']);
    expect($content->termsAndConditions)->toBe($expected['termsAndConditions']);
}

function assertPropertyImagesMatchExpected(array $images, array $expectedImages): void
{
    // Step 1: Validate the total count dynamically.
    expect($images)->toHaveCount(count($expectedImages));

    // Step 2: Validate every single expected image.
    foreach ($expectedImages as $index => $expectedImage) {
        $actualImage = $images[$index];
        expect($actualImage)->toBeInstanceOf(PropertyImage::class);
        expect($actualImage->name)->toBe($expectedImage['name']);
        expect($actualImage->url)->toBe($expectedImage['url']);
        expect($actualImage->modified)->toBe($expectedImage['modified']);
        expect($actualImage->description)->toBe($expectedImage['description']);
    }
}

function assertPropertiesResponseMatchesExpected(\Shelfwood\PhpPms\BookingManager\Responses\PropertiesResponse $actualResponse): void
{
    $expected = TestData::getExpectedPropertiesData();

    expect($actualResponse->properties)->toBeArray();
    expect($actualResponse->properties)->toHaveCount($expected['totalProperties']);

    // Find the detailed property (6794) and validate it comprehensively
    $detailedProperty = null;
    foreach ($actualResponse->properties as $property) {
        if ($property->external_id === $expected['detailedProperty']['external_id']) {
            $detailedProperty = $property;
            break;
        }
    }

    expect($detailedProperty)->not()->toBeNull();

    // Comprehensive validation of the detailed property
    $expectedDetailed = $expected['detailedProperty'];
    expect($detailedProperty->external_id)->toBe($expectedDetailed['external_id']);
    expect($detailedProperty->name)->toBe($expectedDetailed['name']);
    expect($detailedProperty->identifier)->toBe($expectedDetailed['identifier']);
    expect($detailedProperty->status->value)->toBe($expectedDetailed['status']);
    expect($detailedProperty->property_types)->toBe($expectedDetailed['property_types']);
    expect($detailedProperty->max_persons)->toBe($expectedDetailed['max_persons']);
    expect($detailedProperty->minimal_nights)->toBe($expectedDetailed['minimal_nights']);
    expect($detailedProperty->view->value)->toBe($expectedDetailed['view']);
    expect($detailedProperty->internet->value)->toBe($expectedDetailed['internet']);
    expect($detailedProperty->internet_connection->value)->toBe($expectedDetailed['internet_connection']);
    expect($detailedProperty->parking->value)->toBe($expectedDetailed['parking']);
    expect($detailedProperty->swimmingpool->value)->toBe($expectedDetailed['swimmingpool']);
    expect($detailedProperty->sauna->value)->toBe($expectedDetailed['sauna']);
    expect($detailedProperty->tax->vat)->toBe($expectedDetailed['tax']['vat']);
    expect($detailedProperty->tax->other)->toBe($expectedDetailed['tax']['other']);
    expect($detailedProperty->tax->otherType->value)->toBe($expectedDetailed['tax']['otherType']);
}
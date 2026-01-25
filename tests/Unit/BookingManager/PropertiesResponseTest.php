<?php

declare(strict_types=1);

namespace Tests\Unit\BookingManager;

use Shelfwood\PhpPms\BookingManager\Responses\PropertiesResponse;
use Shelfwood\PhpPms\BookingManager\Responses\ValueObjects\PropertyDetails;
use Shelfwood\PhpPms\Http\XMLParser;
use Tests\Helpers\TestHelpers;

describe('PropertiesResponse::map', function () {
    test('parses multiple properties successfully', function () {
        $xml = file_get_contents(TestHelpers::getMockFilePath('all-properties.xml'));
        $parsed = XMLParser::parse($xml);
        $response = PropertiesResponse::map($parsed);

        expect($response)->toBeInstanceOf(PropertiesResponse::class)
            ->and($response->properties)->toBeInstanceOf(\Illuminate\Support\Collection::class)
            ->and($response->properties)->not->toBeEmpty();

        // Verify all items are PropertyDetails instances
        foreach ($response->properties as $property) {
            expect($property)->toBeInstanceOf(PropertyDetails::class)
                ->and($property->external_id)->toBeInt()->toBeGreaterThan(0)
                ->and($property->name)->toBeString()->not->toBeEmpty();
        }
    });

    test('parses empty properties response', function () {
        $xml = file_get_contents(TestHelpers::getMockFilePath('empty-properties.xml'));
        $parsed = XMLParser::parse($xml);
        $response = PropertiesResponse::map($parsed);

        expect($response)->toBeInstanceOf(PropertiesResponse::class)
            ->and($response->properties)->toBeInstanceOf(\Illuminate\Support\Collection::class)
            ->and($response->properties)->toBeEmpty();
    });

    test('handles single property without array wrapper', function () {
        // When XML has only one <property>, parser may not wrap it in array
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<properties count="1">
    <property id="12345" name="Test Property" status="active">
        <provider id="1" name="Test Provider"/>
        <location city="Amsterdam" country="NL"/>
        <max_persons>4</max_persons>
        <minimal_nights>2</minimal_nights>
        <maximal_nights>30</maximal_nights>
        <floor>1</floor>
        <bedrooms>2</bedrooms>
        <bathrooms>1</bathrooms>
        <toilets>1</toilets>
        <supplies bedlinen="1" towels="1"/>
        <service cleanservice="1" linensservice="1"/>
        <cleaning_costs>50.00</cleaning_costs>
        <deposit_costs>100.00</deposit_costs>
        <tax type="none" amount="0.00"/>
        <content>
            <short></short>
            <full></full>
            <area></area>
            <arrival></arrival>
            <termsAndConditions></termsAndConditions>
        </content>
        <images></images>
    </property>
</properties>
XML;

        $parsed = XMLParser::parse($xml);
        $response = PropertiesResponse::map($parsed);

        expect($response->properties)->toHaveCount(1)
            ->and($response->properties->first()->external_id)->toBe(12345)
            ->and($response->properties->first()->name)->toBe('Test Property');
    });

    test('handles missing property array gracefully', function () {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<properties count="0"/>
XML;

        $parsed = XMLParser::parse($xml);
        $response = PropertiesResponse::map($parsed);

        expect($response->properties)->toBeEmpty();
    });

    test('preserves property order from XML', function () {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<properties count="3">
    <property id="100" name="Property A" status="active">
        <provider id="1" name="Test"/>
        <location city="Amsterdam" country="NL"/>
        <max_persons>2</max_persons>
        <minimal_nights>1</minimal_nights>
        <maximal_nights>30</maximal_nights>
        <floor>0</floor>
        <bedrooms>1</bedrooms>
        <bathrooms>1</bathrooms>
        <toilets>1</toilets>
        <supplies bedlinen="1" towels="1"/>
        <service cleanservice="1" linensservice="1"/>
        <cleaning_costs>0</cleaning_costs>
        <deposit_costs>0</deposit_costs>
        <tax type="none" amount="0"/>
        <content>
            <short></short>
            <full></full>
            <area></area>
            <arrival></arrival>
            <termsAndConditions></termsAndConditions>
        </content>
        <images></images>
    </property>
    <property id="200" name="Property B" status="active">
        <provider id="1" name="Test"/>
        <location city="Rotterdam" country="NL"/>
        <max_persons>2</max_persons>
        <minimal_nights>1</minimal_nights>
        <maximal_nights>30</maximal_nights>
        <floor>0</floor>
        <bedrooms>1</bedrooms>
        <bathrooms>1</bathrooms>
        <toilets>1</toilets>
        <supplies bedlinen="1" towels="1"/>
        <service cleanservice="1" linensservice="1"/>
        <cleaning_costs>0</cleaning_costs>
        <deposit_costs>0</deposit_costs>
        <tax type="none" amount="0"/>
        <content>
            <short></short>
            <full></full>
            <area></area>
            <arrival></arrival>
            <termsAndConditions></termsAndConditions>
        </content>
        <images></images>
    </property>
    <property id="300" name="Property C" status="active">
        <provider id="1" name="Test"/>
        <location city="Utrecht" country="NL"/>
        <max_persons>2</max_persons>
        <minimal_nights>1</minimal_nights>
        <maximal_nights>30</maximal_nights>
        <floor>0</floor>
        <bedrooms>1</bedrooms>
        <bathrooms>1</bathrooms>
        <toilets>1</toilets>
        <supplies bedlinen="1" towels="1"/>
        <service cleanservice="1" linensservice="1"/>
        <cleaning_costs>0</cleaning_costs>
        <deposit_costs>0</deposit_costs>
        <tax type="none" amount="0"/>
        <content>
            <short></short>
            <full></full>
            <area></area>
            <arrival></arrival>
            <termsAndConditions></termsAndConditions>
        </content>
        <images></images>
    </property>
</properties>
XML;

        $parsed = XMLParser::parse($xml);
        $response = PropertiesResponse::map($parsed);

        expect($response->properties)->toHaveCount(3);

        $ids = $response->properties->pluck('external_id')->toArray();
        expect($ids)->toBe([100, 200, 300]);

        $names = $response->properties->pluck('name')->toArray();
        expect($names)->toBe(['Property A', 'Property B', 'Property C']);
    });

    test('collection methods work as expected', function () {
        $xml = file_get_contents(TestHelpers::getMockFilePath('all-properties.xml'));
        $parsed = XMLParser::parse($xml);
        $response = PropertiesResponse::map($parsed);

        // Test collection operations
        $first = $response->properties->first();
        expect($first)->toBeInstanceOf(PropertyDetails::class);

        $filtered = $response->properties->filter(fn($p) => $p->external_id > 0);
        expect($filtered->count())->toBe($response->properties->count());

        $mapped = $response->properties->map(fn($p) => $p->external_id);
        expect($mapped)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    });

    test('handles invalid structure gracefully', function () {
        $parsed = ['invalid' => 'structure'];

        $response = PropertiesResponse::map($parsed);

        expect($response->properties)->toBeEmpty();
    });

    test('handles incomplete property data with defaults', function () {
        // XML with property missing nested elements - should use defaults
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<properties count="1">
    <property id="999" name="Incomplete Property" status="active">
        <provider id="1" name="Test"/>
        <location city="Test" country="NL"/>
        <max_persons>0</max_persons>
        <minimal_nights>0</minimal_nights>
        <maximal_nights>0</maximal_nights>
        <floor>0</floor>
        <bedrooms>0</bedrooms>
        <bathrooms>0</bathrooms>
        <toilets>0</toilets>
        <supplies bedlinen="0" towels="0"/>
        <service cleanservice="0" linensservice="0"/>
        <cleaning_costs>0</cleaning_costs>
        <deposit_costs>0</deposit_costs>
        <tax type="none" amount="0"/>
        <content/>
        <images/>
    </property>
</properties>
XML;

        $parsed = XMLParser::parse($xml);
        $response = PropertiesResponse::map($parsed);

        expect($response->properties)->toHaveCount(1);
        expect($response->properties->first()->external_id)->toBe(999);
    });
});

<?php

require 'vendor/autoload.php';

use Shelfwood\PhpPms\Http\XMLParser;
use Shelfwood\PhpPms\BookingManager\Responses\RateResponse;

// Mock XML response (using test structure)
$mockXml = '<?xml version="1.0" encoding="UTF-8"?>
<info arrival="2024-02-19" departure="2024-02-20" nights="1">
    <property id="21663" identifier="#487" max_persons="2" available="1" minimal_nights="1">
        <rate currency="EUR">
            <total>220.00</total>
            <final>220.00</final>
            <tax total="35.20">
                <vat value="9">19.80</vat>
                <other type="relative" value="7">15.40</other>
                <final>255.20</final>
            </tax>
            <fee>0.00</fee>
            <prepayment>66.00</prepayment>
            <balance_due>189.20</balance_due>
        </rate>
    </property>
</info>';

// Parse XML and map to response
$parsedData = XMLParser::parse($mockXml);
$response = RateResponse::map($parsedData);

echo "Available field: ";
var_dump($response->available);
echo "Type: " . gettype($response->available) . PHP_EOL;

// Test the calendar conversion
echo "Calendar conversion: ";
$calendarAvailable = ($response->available === true) ? 1 : 0;
echo $calendarAvailable . PHP_EOL;
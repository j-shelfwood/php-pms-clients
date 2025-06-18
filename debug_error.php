<?php
require 'vendor/autoload.php';
use Shelfwood\PhpPms\Http\XMLParser;

$xml = file_get_contents('mocks/bookingmanager/generic-error.xml');
$parsed = XMLParser::parse($xml);
var_dump($parsed);
echo "Has error: " . (XMLParser::hasError($parsed) ? 'true' : 'false') . "\n";
if (XMLParser::hasError($parsed)) {
    $error = XMLParser::extractErrorDetails($parsed);
    echo "Error code: " . $error->code . "\n";
    echo "Error message: " . $error->message . "\n";
}
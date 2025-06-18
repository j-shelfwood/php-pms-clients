<?php

require 'vendor/autoload.php';

use Carbon\Carbon;

$date = Carbon::parse('2024-02-20');
$start = Carbon::parse('2024-02-20');
$end = Carbon::parse('2024-02-20');

echo "Date: {$date->toDateString()}" . PHP_EOL;
echo "Start: {$start->toDateString()}" . PHP_EOL;
echo "End: {$end->toDateString()}" . PHP_EOL;

echo "between() result: " . ($date->between($start, $end) ? 'true' : 'false') . PHP_EOL;
echo "betweenIncluded() result: " . ($date->betweenIncluded($start, $end) ? 'true' : 'false') . PHP_EOL;
echo "gte(start) && lte(end): " . (($date->gte($start) && $date->lte($end)) ? 'true' : 'false') . PHP_EOL;

// Test with different times
$dateWithTime = Carbon::parse('2024-02-20 12:00:00');
$startWithTime = Carbon::parse('2024-02-20 00:00:00');
$endWithTime = Carbon::parse('2024-02-20 23:59:59');

echo PHP_EOL . "With time components:" . PHP_EOL;
echo "Date: {$dateWithTime->toDateTimeString()}" . PHP_EOL;
echo "Start: {$startWithTime->toDateTimeString()}" . PHP_EOL;
echo "End: {$endWithTime->toDateTimeString()}" . PHP_EOL;
echo "between() result: " . ($dateWithTime->between($startWithTime, $endWithTime) ? 'true' : 'false') . PHP_EOL;
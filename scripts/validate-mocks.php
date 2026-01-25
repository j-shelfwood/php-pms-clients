#!/usr/bin/env php
<?php

/**
 * Mock Validation Utility
 *
 * Validates mock files against real API responses to detect structural drift.
 *
 * Usage:
 *   php scripts/validate-mocks.php --provider=bookingmanager
 *   php scripts/validate-mocks.php --provider=mews
 *   php scripts/validate-mocks.php --all
 *   php scripts/validate-mocks.php --help
 *
 * Environment Variables:
 *   BM_API_KEY          BookingManager API key
 *   BM_BASE_URL         BookingManager API base URL
 *   MEWS_CLIENT_TOKEN   Mews Connector API client token
 *   MEWS_ACCESS_TOKEN   Mews Connector API access token
 *   MEWS_BASE_URL       Mews API base URL (default: https://api.mews-demo.com)
 */

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;
use Shelfwood\PhpPms\BookingManager\Responses\CalendarChangesResponse;
use Shelfwood\PhpPms\BookingManager\Responses\PropertiesResponse;
use Shelfwood\PhpPms\Http\XMLParser;
use Carbon\Carbon;
use Psr\Log\NullLogger;

class MockValidator
{
    private array $results = [];
    private int $passed = 0;
    private int $failed = 0;

    public function validateBookingManagerMocks(): void
    {
        echo "\n📋 Validating BookingManager Mocks\n";
        echo str_repeat('=', 80) . "\n\n";

        $apiKey = getenv('BM_API_KEY');
        $baseUrl = getenv('BM_BASE_URL');

        if (empty($apiKey) || empty($baseUrl)) {
            $this->skip('BookingManager', 'Missing BM_API_KEY or BM_BASE_URL environment variables');
            return;
        }

        $api = new BookingManagerAPI(
            new Client(['timeout' => 30]),
            $apiKey,
            $baseUrl,
            new NullLogger()
        );

        // Validate calendar-changes.xml
        $this->validateCalendarChanges($api);

        // Validate all-properties.xml
        $this->validateProperties($api);
    }

    private function validateCalendarChanges(BookingManagerAPI $api): void
    {
        echo "🔍 Validating calendar-changes.xml... ";

        try {
            // Fetch real API response
            $since = Carbon::now()->subDays(30);
            $realResponse = $api->calendarChanges($since);

            // Load mock file
            $mockPath = __DIR__ . '/../mocks/bookingmanager/calendar-changes.xml';
            if (!file_exists($mockPath)) {
                $this->fail('calendar-changes.xml', "Mock file not found: {$mockPath}");
                return;
            }

            $mockXml = file_get_contents($mockPath);
            $mockData = XMLParser::parse($mockXml);
            $mockResponse = CalendarChangesResponse::map($mockData);

            // Structural validation
            $differences = $this->compareStructure($realResponse, $mockResponse, 'CalendarChangesResponse');

            if (empty($differences)) {
                $this->pass('calendar-changes.xml');
            } else {
                $this->fail('calendar-changes.xml', "Structure differs from real API:\n" . implode("\n", $differences));
            }
        } catch (\Throwable $e) {
            $this->fail('calendar-changes.xml', "Exception: " . $e->getMessage());
        }
    }

    private function validateProperties(BookingManagerAPI $api): void
    {
        echo "🔍 Validating all-properties.xml... ";

        try {
            // Fetch real API response
            $realResponse = $api->properties();

            // Load mock file
            $mockPath = __DIR__ . '/../mocks/bookingmanager/all-properties.xml';
            if (!file_exists($mockPath)) {
                $this->fail('all-properties.xml', "Mock file not found: {$mockPath}");
                return;
            }

            $mockXml = file_get_contents($mockPath);
            $mockData = XMLParser::parse($mockXml);
            $mockResponse = PropertiesResponse::map($mockData);

            // Structural validation
            $differences = $this->compareStructure($realResponse, $mockResponse, 'PropertiesResponse');

            if (empty($differences)) {
                $this->pass('all-properties.xml');
            } else {
                $this->fail('all-properties.xml', "Structure differs from real API:\n" . implode("\n", $differences));
            }
        } catch (\Throwable $e) {
            $this->fail('all-properties.xml', "Exception: " . $e->getMessage());
        }
    }

    private function compareStructure(object $real, object $mock, string $className): array
    {
        $differences = [];

        // Get public properties
        $realProps = get_object_vars($real);
        $mockProps = get_object_vars($mock);

        // Check for missing properties in mock
        foreach (array_keys($realProps) as $prop) {
            if (!array_key_exists($prop, $mockProps)) {
                $differences[] = "  ❌ Real API has property '{$prop}' not present in mock";
            }
        }

        // Check for extra properties in mock
        foreach (array_keys($mockProps) as $prop) {
            if (!array_key_exists($prop, $realProps)) {
                $differences[] = "  ⚠️  Mock has property '{$prop}' not present in real API response";
            }
        }

        // Type comparison for common properties
        foreach ($realProps as $prop => $realValue) {
            if (!array_key_exists($prop, $mockProps)) {
                continue;
            }

            $mockValue = $mockProps[$prop];
            $realType = $this->getTypeDescription($realValue);
            $mockType = $this->getTypeDescription($mockValue);

            if ($realType !== $mockType) {
                $differences[] = "  ⚠️  Property '{$prop}' type mismatch: real={$realType}, mock={$mockType}";
            }

            // For collections, check first item structure if available
            if ($realValue instanceof \Illuminate\Support\Collection && $mockValue instanceof \Illuminate\Support\Collection) {
                if ($realValue->isNotEmpty() && $mockValue->isNotEmpty()) {
                    $realItem = $realValue->first();
                    $mockItem = $mockValue->first();

                    if (is_object($realItem) && is_object($mockItem)) {
                        $itemDifferences = $this->compareStructure($realItem, $mockItem, get_class($realItem));
                        foreach ($itemDifferences as $diff) {
                            $differences[] = "  In collection '{$prop}': " . $diff;
                        }
                    }
                }
            }
        }

        return $differences;
    }

    private function getTypeDescription($value): string
    {
        if (is_null($value)) {
            return 'null';
        }
        if (is_object($value)) {
            if ($value instanceof \Illuminate\Support\Collection) {
                $count = $value->count();
                $itemType = $value->isEmpty() ? 'empty' : $this->getTypeDescription($value->first());
                return "Collection<{$itemType}>[{$count}]";
            }
            if ($value instanceof \Carbon\Carbon) {
                return 'Carbon';
            }
            return get_class($value);
        }
        return gettype($value);
    }

    private function pass(string $mock): void
    {
        echo "✅ PASS\n";
        $this->passed++;
        $this->results[] = ['mock' => $mock, 'status' => 'pass'];
    }

    private function fail(string $mock, string $reason): void
    {
        echo "❌ FAIL\n";
        echo "  Reason: {$reason}\n";
        $this->failed++;
        $this->results[] = ['mock' => $mock, 'status' => 'fail', 'reason' => $reason];
    }

    private function skip(string $provider, string $reason): void
    {
        echo "⏭️  Skipped {$provider}: {$reason}\n";
    }

    public function printSummary(): void
    {
        echo "\n" . str_repeat('=', 80) . "\n";
        echo "📊 Summary\n";
        echo str_repeat('=', 80) . "\n\n";

        if ($this->passed > 0) {
            echo "✅ Passed: {$this->passed}\n";
        }
        if ($this->failed > 0) {
            echo "❌ Failed: {$this->failed}\n";
        }

        if ($this->failed > 0) {
            echo "\n⚠️  Mock validation failed. Please update mocks to match current API responses.\n";
            exit(1);
        } else {
            echo "\n🎉 All mocks validated successfully!\n";
            exit(0);
        }
    }
}

// Parse command line arguments
$options = getopt('', ['provider:', 'all', 'help']);

if (isset($options['help'])) {
    echo <<<HELP

Mock Validation Utility

Usage:
  php scripts/validate-mocks.php --provider=bookingmanager
  php scripts/validate-mocks.php --provider=mews
  php scripts/validate-mocks.php --all
  php scripts/validate-mocks.php --help

Options:
  --provider=PROVIDER    Validate mocks for specific provider (bookingmanager|mews)
  --all                  Validate all provider mocks
  --help                 Show this help message

Environment Variables:
  BM_API_KEY             BookingManager API key
  BM_BASE_URL            BookingManager API base URL
  MEWS_CLIENT_TOKEN      Mews Connector API client token
  MEWS_ACCESS_TOKEN      Mews Connector API access token
  MEWS_BASE_URL          Mews API base URL (default: https://api.mews-demo.com)

Examples:
  # Validate BookingManager mocks
  BM_API_KEY=xxx BM_BASE_URL=https://xmlsync.bookingmanager.com php scripts/validate-mocks.php --provider=bookingmanager

  # Validate all mocks
  php scripts/validate-mocks.php --all

HELP;
    exit(0);
}

$validator = new MockValidator();

$provider = $options['provider'] ?? null;
$all = isset($options['all']);

if ($all || $provider === 'bookingmanager') {
    $validator->validateBookingManagerMocks();
}

if ($all || $provider === 'mews') {
    echo "\n⏭️  Mews validation not yet implemented\n";
}

if (!$all && !$provider) {
    echo "❌ Error: Please specify --provider=PROVIDER or --all\n";
    echo "Run with --help for usage information\n";
    exit(1);
}

$validator->printSummary();

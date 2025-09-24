# PHP PMS Clients

This repository contains PHP clients for Property Management Systems (PMS).

## Supported Property Management Systems

- [x] [BookingManager](https://www.bookingmanager.com/)
- [x] [Mews](https://www.mews.com/)

## Installation

```bash
composer require shelfwood/php-pms-clients
```

## Laravel Integration

### Automatic Service Provider Registration

The package will automatically register its service provider in Laravel applications using auto-discovery.

### Configuration Publishing

Publish the configuration file to customize HTTP client behavior:

```bash
php artisan vendor:publish --tag=php-pms-config
```

This creates `config/php-pms.php` with the following options:

```php
return [
    'http' => [
        'timeout' => env('PHP_PMS_HTTP_TIMEOUT', 30),
        'verify_ssl' => env('PHP_PMS_VERIFY_SSL', true),
        'debug' => env('PHP_PMS_HTTP_DEBUG', false), // Only works in local environment
    ],
    // ... other configuration options
];
```

### Environment Variables

Add these to your `.env` file:

```bash
# HTTP Client Configuration
PHP_PMS_HTTP_TIMEOUT=30
PHP_PMS_VERIFY_SSL=true
PHP_PMS_HTTP_DEBUG=false

# BookingManager Configuration
BOOKING_MANAGER_BASE_URL=https://xml.billypds.com
BOOKING_MANAGER_API_KEY=your-api-key-here

# Logging
PHP_PMS_LOGGING=false
PHP_PMS_LOG_CHANNEL=default
```

### Controlling Debug Output

The verbose HTTP debug output (cURL verbose mode) can be controlled via environment:

- **Production/Staging**: Debug output is always disabled for security
- **Local Environment**: Set `PHP_PMS_HTTP_DEBUG=true` to enable debug output
- **Default**: Debug output is disabled by default

This prevents the verbose cURL output that appears in `art queue:listen` when `APP_DEBUG=true`.

## Debugging

A CLI tool is included to help debug the BookingManager API integration using a production key. It runs a sequence of non-destructive read calls to verify data is being returned and mapped correctly.

The sequence is:

1.  Fetch all properties.
2.  Fetch details for a specific property (either the first one found or one you specify).
3.  Fetch calendar changes for the past week.
4.  Fetch the calendar/availability for that property for the next 30 days.
5.  Fetch a sample rate for a future stay at that property.

**Usage:**

Make the script executable:

```bash
chmod +x bin/debug-bookingmanager
```

Run the script with your API key:

```bash
./bin/debug-bookingmanager YOUR_API_KEY_HERE
```

Optionally, you can specify a property ID to test:

```bash
./bin/debug-bookingmanager YOUR_API_KEY_HERE 21663
```

The script will output the parsed XML data and the final mapped PHP object for each step, which is useful for verifying that all data fields are correctly set up in the PMS.

## Testing

The test suite uses [Pest](https://pestphp.com/) and employs a comprehensive **Golden Master** testing approach. For each API endpoint, we validate the entire response object against a canonical "golden" version, ensuring 100% of fields are mapped correctly for both rich and edge-case responses.

The testing strategy includes:

- **Golden Master Validation**: Each endpoint test validates every field in the response against expected data
- **Edge Case Coverage**: Tests for minimal properties, inactive properties, empty responses, and comprehensive data
- **485+ Assertions**: Comprehensive validation covering all response object properties
- **Curated Mock Data**: Real-world XML responses extracted from production data for accurate testing

**Test Structure:**

- `tests/Endpoint/BookingManager/` — Endpoint-level integration tests for BookingManager API
- `tests/Helpers/` — Reusable assertion helpers and test data
- `mocks/bookingmanager/` — Comprehensive mock XML responses including edge cases

**Running Tests:**

```bash
# Run all tests
vendor/bin/pest

# Run tests for a specific endpoint
vendor/bin/pest --filter="PropertyEndpointTest"

# Discover property variance in mock data
./bin/discover-property-variance
```

## Project Structure

- `src/` — Library source code, organized by PMS and concern.
- `tests/Endpoint/BookingManager/` — Endpoint-level integration tests for BookingManager API.
- `mocks/` — Mock XML responses for deterministic test scenarios.
- `bin/` — Executable CLI tools for debugging and other tasks.

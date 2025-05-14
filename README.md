# PHP PMS Clients

This repository contains PHP clients for Property Management Systems (PMS).

## Supported Property Management Systems

- [x] [BookingManager](https://www.bookingmanager.com/)
- [x] [Mews](https://www.mews.com/)

## Installation

```bash
composer require shelfwood/php-pms-clients
```

## Testing

The test suite uses [Pest](https://pestphp.com/) and is organized by endpoint, with a single integration-style test file per endpoint. All redundant or duplicate tests have been removed for clarity and maintainability.

To run the tests:

```bash
vendor/bin/pest
```

## Project Structure

- `src/` — Library source code, organized by PMS and concern.
- `tests/Endpoint/BookingManager/` — Endpoint-level integration tests for BookingManager API.
- `mocks/` — Mock XML responses for deterministic test scenarios.

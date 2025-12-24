# Release Notes: php-pms-clients v5.0.0

## Breaking

- Mews availability upgraded to `services/getAvailability/2024-01-22`:
  - Request now requires `Metrics` (defaults to all metrics if omitted).
  - Response now maps `ResourceCategoryAvailabilities` with a per-category `Metrics` map.
  - API endpoint path changed to `/api/connector/v1/services/getAvailability/2024-01-22`.
- Availability + Pricing time-unit boundaries normalized to Mews-required “00:00 converted to UTC” using enterprise timezone from `configuration/get` (cached per client instance).

## Notable changes included in this release

- Reservations `getAll` uses versioned endpoint `/reservations/getAll/2023-06-06` with pagination/extent alignment.
- Customers “search by email” aligned to use `customers/getAll` + `Extent` + `Limitation`.
- Pricing requests no longer send `OccupancyConfiguration` for `rates/getPricing` (per current contract assumptions/tests).

## Test coverage

- Adds assertions that outgoing Availability/Pricing requests include required fields and correct UTC-normalized boundaries.
- Updates fixtures to reflect latest Mews contracts.


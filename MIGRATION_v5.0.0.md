# Migration Guide: php-pms-clients v4.x → v5.0.0

**Package:** `shelfwood/php-pms-clients`  
**Version:** v5.0.0  
**Severity:** BREAKING CHANGES

---

## Executive Summary

Version 5.0.0 introduces breaking changes to the Mews Connector integration to align with the official availability contract (`services/getAvailability/2024-01-22`) and to enforce Mews-required time unit boundaries (“00:00 converted to UTC”) for availability and pricing requests.

---

## Breaking Changes

### 1) Availability endpoint + schema upgraded (2024-01-22)

**Impact:** HIGH

#### Endpoint change

- **Before (v4.x):** `/api/connector/v1/services/getAvailability`
- **After (v5.0.0):** `/api/connector/v1/services/getAvailability/2024-01-22`

#### Request contract change: `Metrics` required

The new endpoint requires a non-empty `Metrics` list.

**Before (v4.x):**
```php
use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Payloads\GetAvailabilityPayload;

$payload = new GetAvailabilityPayload(
    serviceId: $serviceId,
    firstTimeUnitStartUtc: Carbon::parse('2025-12-19'),
    lastTimeUnitStartUtc: Carbon::parse('2025-12-23'),
    resourceCategoryIds: null, // optional
);
```

**After (v5.0.0):**
```php
use Carbon\Carbon;
use Shelfwood\PhpPms\Mews\Payloads\GetAvailabilityPayload;
use Shelfwood\PhpPms\Mews\Enums\ResourceAvailabilityMetricType;

$payload = new GetAvailabilityPayload(
    serviceId: $serviceId,
    firstTimeUnitStartUtc: Carbon::parse('2025-12-19'),
    lastTimeUnitStartUtc: Carbon::parse('2025-12-23'),
    metrics: [
        ResourceAvailabilityMetricType::Occupied,
        ResourceAvailabilityMetricType::ActiveResources,
    ],
);
```

Notes:
- If you omit `metrics`, the client defaults to requesting all supported metrics.
- `ResourceCategoryIds` filtering was removed from `GetAvailabilityPayload`.

#### Response contract change: `CategoryAvailabilities` replaced

**Before (v4.x):**
- Response returned `CategoryAvailabilities[]` and mapped to `$response->categoryAvailabilities` (collection of `AvailabilityBlock`).

**After (v5.0.0):**
- Response returns `ResourceCategoryAvailabilities[]` and maps to `$response->resourceCategoryAvailabilities` (collection of `ResourceCategoryAvailability`).
- Each category availability contains a `Metrics` map keyed by metric name, with arrays of integers per time unit.

**Before (v4.x):**
```php
$first = $availability->categoryAvailabilities->first();
$categoryId = $first->categoryId;
$availabilities = $first->availabilities;
```

**After (v5.0.0):**
```php
use Shelfwood\PhpPms\Mews\Enums\ResourceAvailabilityMetricType;

$first = $availability->resourceCategoryAvailabilities->first();
$categoryId = $first->resourceCategoryId;
$occupied = $first->metrics[ResourceAvailabilityMetricType::Occupied->value] ?? [];
```

---

### 2) Enterprise-midnight time unit boundaries enforced

**Impact:** MEDIUM/HIGH (behavioral change)

Mews requires `FirstTimeUnitStartUtc` / `LastTimeUnitStartUtc` to correspond to the start boundary of the service time unit (for day-based services: **midnight in enterprise timezone, expressed as UTC**).

In v5.0.0:
- Availability and Pricing requests normalize input dates to the enterprise’s midnight boundary before sending.
- Enterprise timezone is retrieved from `/api/connector/v1/configuration/get` and cached per `MewsHttpClient` instance.

**Behavioral implications**
- The time component of input `Carbon` values is treated as a date; the implementation normalizes based on the `Y-m-d` portion.
- You will see **one additional API call** (`configuration/get`) per `MewsHttpClient` instance (cached thereafter).

---

## Upgrade Procedure

1) Update dependency:
```bash
composer require shelfwood/php-pms-clients:^5.0
```

2) Update availability response usage:
- Search for `categoryAvailabilities` and migrate to `resourceCategoryAvailabilities`.

3) Update availability metrics usage:
- Read `ResourceCategoryAvailability::$metrics` by metric key.
- Prefer using `ResourceAvailabilityMetricType` keys.

4) Review any date boundary logic:
- If you previously passed UTC timestamps expecting them to be used verbatim, adjust call sites to pass date-only semantics (or accept the new normalization).


# Migration Guide: php-pms-clients v3.x → v4.0.0

**Package:** `shelfwood/php-pms-clients`
**Version:** v4.0.0
**Release Date:** December 23, 2025
**Severity:** BREAKING CHANGES - Immediate action required

---

## Executive Summary

Version 4.0.0 introduces critical structural changes to the Mews Connector API integration, focusing on type safety and proper API alignment. This release refactors the Restriction DTO to use composition patterns and enforces enum type safety across all DTOs.

**Impact Level:** HIGH - Code changes required in consuming applications
**Test Coverage:** 268 tests, 1857 assertions - all passing
**Upgrade Time Estimate:** 30-60 minutes depending on codebase usage

---

## Breaking Changes

### 1. Restriction DTO Structural Refactoring

**Impact:** HIGH - All code accessing Restriction properties must be updated

#### Before (v3.x)
```php
// Flat property structure
$restriction->resourceCategoryId;
$restriction->minimumStay;
$restriction->exactRateId;
$restriction->baseRateId;
$restriction->type;  // string
$restriction->startUtc;
$restriction->endUtc;
$restriction->days;
$restriction->minAdvance;
$restriction->maxAdvance;
$restriction->minLength;  // string (ISO 8601 duration)
$restriction->maxLength;
```

#### After (v4.0.0)
```php
use Shelfwood\PhpPms\Mews\Enums\RestrictionType;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\RestrictionConditions;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\RestrictionExceptions;

// Composition pattern with nested objects
$restriction->conditions->resourceCategoryId;
$restriction->conditions->exactRateId;
$restriction->conditions->baseRateId;
$restriction->conditions->rateGroupId;
$restriction->conditions->type;  // RestrictionType enum
$restriction->conditions->startUtc;
$restriction->conditions->endUtc;
$restriction->conditions->days;
$restriction->conditions->hours;

$restriction->exceptions->minAdvance;   // ISO 8601 string
$restriction->exceptions->maxAdvance;   // ISO 8601 string
$restriction->exceptions->minLength;    // ISO 8601 string
$restriction->exceptions->maxLength;    // ISO 8601 string
$restriction->exceptions->minPrice;     // array
$restriction->exceptions->maxPrice;     // array
$restriction->exceptions->minReservationCount;
$restriction->exceptions->maxReservationCount;
```

#### Migration Actions Required

**Search Pattern:** `$restriction->` in your codebase

1. **Update property access paths:**
   ```php
   // OLD
   if ($restriction->resourceCategoryId === $categoryId) {
       $minStay = $restriction->minLength;
   }

   // NEW
   if ($restriction->conditions->resourceCategoryId === $categoryId) {
       $minStay = $restriction->exceptions->minLength;
   }
   ```

2. **Update type comparisons:**
   ```php
   // OLD
   if ($restriction->type === 'Start') {
       // ...
   }

   // NEW
   use Shelfwood\PhpPms\Mews\Enums\RestrictionType;

   if ($restriction->conditions->type === RestrictionType::Start) {
       // ...
   }
   ```

3. **Update date range checks:**
   ```php
   // OLD
   $start = Carbon::parse($restriction->startUtc);
   $end = Carbon::parse($restriction->endUtc);

   // NEW
   $start = Carbon::parse($restriction->conditions->startUtc);
   $end = Carbon::parse($restriction->conditions->endUtc);
   ```

---

### 2. ReservationsClient::updateState() Type Signature Change

**Impact:** MEDIUM - All calls to updateState() must be updated

#### Before (v3.x)
```php
$client->updateState(
    reservationId: $id,
    newState: 'Confirmed'  // string parameter
);
```

#### After (v4.0.0)
```php
use Shelfwood\PhpPms\Mews\Enums\ReservationState;

$client->updateState(
    reservationId: $id,
    newState: ReservationState::Confirmed  // enum parameter
);
```

#### Migration Actions Required

**Search Pattern:** `->updateState(`

Update all calls to use ReservationState enum:
- `'Inquired'` → `ReservationState::Inquired`
- `'Optional'` → `ReservationState::Optional`
- `'Confirmed'` → `ReservationState::Confirmed`
- `'Started'` → `ReservationState::Started`
- `'Processed'` → `ReservationState::Processed`
- `'Canceled'` → `ReservationState::Canceled`
- `'Requested'` → `ReservationState::Requested`

---

## Non-Breaking Enhancements

### 1. Enum Type Safety Enforcement

All Mews DTOs now properly use enum types instead of strings:

```php
use Shelfwood\PhpPms\Mews\Enums\{
    AgeClassification,
    RateType,
    ReservationState,
    ResourceState,
    ServiceType,
    RestrictionType
};

// String comparisons replaced with enum comparisons
$customer->ageCategory->classification === AgeClassification::Adult;
$rate->type === RateType::Public;
$reservation->state === ReservationState::Confirmed;
$resource->state === ResourceState::Clean;
$service->type === ServiceType::Accommod;
```

**Benefits:**
- IDE autocomplete support
- Type safety at compile time
- Reduced runtime errors from typos

---

## Upgrade Procedure

### Step 1: Update Composer Dependency

```bash
composer require shelfwood/php-pms-clients:^4.0
```

### Step 2: Search for Restriction Property Access

```bash
# Find all usages of Restriction DTO
grep -r "\$restriction->" --include="*.php" app/
grep -r "Restriction::" --include="*.php" app/
```

### Step 3: Update Restriction Property Paths

For each occurrence, update property access to use nested structure:

| Old Path | New Path |
|----------|----------|
| `$restriction->resourceCategoryId` | `$restriction->conditions->resourceCategoryId` |
| `$restriction->exactRateId` | `$restriction->conditions->exactRateId` |
| `$restriction->baseRateId` | `$restriction->conditions->baseRateId` |
| `$restriction->type` | `$restriction->conditions->type` (RestrictionType enum) |
| `$restriction->startUtc` | `$restriction->conditions->startUtc` |
| `$restriction->endUtc` | `$restriction->conditions->endUtc` |
| `$restriction->days` | `$restriction->conditions->days` |
| `$restriction->minAdvance` | `$restriction->exceptions->minAdvance` |
| `$restriction->maxAdvance` | `$restriction->exceptions->maxAdvance` |
| `$restriction->minLength` | `$restriction->exceptions->minLength` |
| `$restriction->maxLength` | `$restriction->exceptions->maxLength` |

### Step 4: Update ReservationsClient Calls

```bash
# Find all updateState calls
grep -r "->updateState(" --include="*.php" app/
```

Update to use enum:
```php
use Shelfwood\PhpPms\Mews\Enums\ReservationState;

$client->updateState($id, ReservationState::Confirmed);
```

### Step 5: Add Enum Imports

Add necessary enum imports to files using Mews DTOs:

```php
use Shelfwood\PhpPms\Mews\Enums\{
    AgeClassification,
    RateType,
    ReservationState,
    ResourceState,
    ServiceType,
    RestrictionType
};
```

### Step 6: Run Tests

```bash
# Run your application test suite
php artisan test

# Or PHPUnit
vendor/bin/phpunit

# Or Pest
vendor/bin/pest
```

---

## Testing Strategy

### 1. Integration Tests

Update any integration tests that mock or assert on Restriction DTOs:

```php
// OLD assertion
$this->assertEquals('Start', $restriction->type);
$this->assertEquals($categoryId, $restriction->resourceCategoryId);

// NEW assertion
use Shelfwood\PhpPms\Mews\Enums\RestrictionType;

$this->assertEquals(RestrictionType::Start, $restriction->conditions->type);
$this->assertEquals($categoryId, $restriction->conditions->resourceCategoryId);
```

### 2. Feature Tests

Test reservation state transitions:

```php
use Shelfwood\PhpPms\Mews\Enums\ReservationState;

// Update reservation state
$client->updateState($reservationId, ReservationState::Confirmed);

// Assert new state
$reservation = $client->getById($reservationId);
$this->assertEquals(ReservationState::Confirmed, $reservation->state);
```

---

## Common Migration Patterns

### Pattern 1: Minimum Stay Calculation

```php
// BEFORE
foreach ($restrictions as $restriction) {
    if ($restriction->resourceCategoryId !== $categoryId) {
        continue;
    }

    $date = Carbon::parse($restriction->startUtc);
    if ($date->isSameDay($checkDate)) {
        if ($restriction->minLength !== null) {
            return $restriction->minLength; // ISO 8601 string
        }
    }
}

// AFTER
foreach ($restrictions as $restriction) {
    if ($restriction->conditions->resourceCategoryId !== $categoryId) {
        continue;
    }

    $date = Carbon::parse($restriction->conditions->startUtc);
    if ($date->isSameDay($checkDate)) {
        if ($restriction->exceptions->minLength !== null) {
            return $restriction->exceptions->minLength; // ISO 8601 string
        }
    }
}
```

### Pattern 2: Rate Filtering

```php
// BEFORE
$publicRates = array_filter($rates, function ($rate) {
    return $rate->type === 'Public' && $rate->isActive;
});

// AFTER
use Shelfwood\PhpPms\Mews\Enums\RateType;

$publicRates = $rates->filter(function ($rate) {
    return $rate->type === RateType::Public && $rate->isActive;
});
```

### Pattern 3: Reservation State Workflow

```php
// BEFORE
if ($reservation->state === 'Optional') {
    $client->updateState($reservation->id, 'Confirmed');
}

// AFTER
use Shelfwood\PhpPms\Mews\Enums\ReservationState;

if ($reservation->state === ReservationState::Optional) {
    $client->updateState($reservation->id, ReservationState::Confirmed);
}
```

---

## New Features & Improvements

### 1. RestrictionType Enum

New enum for restriction types:

```php
namespace Shelfwood\PhpPms\Mews\Enums;

enum RestrictionType: string
{
    case Start = 'Start';
    case Stay = 'Stay';
}
```

### 2. RestrictionConditions Value Object

Encapsulates restriction conditions:

```php
class RestrictionConditions
{
    public readonly RestrictionType $type;
    public readonly ?string $exactRateId;
    public readonly ?string $baseRateId;
    public readonly ?string $rateGroupId;
    public readonly ?string $resourceCategoryId;
    public readonly ?string $resourceCategoryType;
    public readonly string $startUtc;
    public readonly string $endUtc;
    public readonly array $days;
    public readonly array $hours;
}
```

### 3. RestrictionExceptions Value Object

Encapsulates restriction exceptions:

```php
class RestrictionExceptions
{
    public readonly ?string $minAdvance;      // ISO 8601 duration
    public readonly ?string $maxAdvance;      // ISO 8601 duration
    public readonly ?string $minLength;       // ISO 8601 duration
    public readonly ?string $maxLength;       // ISO 8601 duration
    public readonly ?array $minPrice;
    public readonly ?array $maxPrice;
    public readonly ?int $minReservationCount;
    public readonly ?int $maxReservationCount;
}
```

### 4. Collection Support in PricingClient

PricingClient now properly uses Laravel Collections:

```php
// Filtering rates uses Collection methods
$publicRates = $rates->items->filter(fn($rate) =>
    $rate->type === RateType::Public && $rate->isActive
);
```

---

## Rollback Strategy

If issues arise during upgrade:

```bash
# Revert to v3.8.0 (last v3.x version)
composer require shelfwood/php-pms-clients:^3.8

# Clear caches
php artisan cache:clear
php artisan config:clear
composer dump-autoload
```

---

## Support & Resources

- **GitHub Repository:** https://github.com/j-shelfwood/php-pms-clients
- **Latest Release:** v4.0.0
- **Issues:** Report migration issues on GitHub
- **Documentation:** See README.md and inline PHPDocs

---

## Verification Checklist

- [ ] Composer dependency updated to ^4.0
- [ ] All Restriction property access paths updated
- [ ] All ReservationsClient::updateState() calls use enum
- [ ] Enum imports added to relevant files
- [ ] Integration tests updated
- [ ] Feature tests passing
- [ ] Manual testing completed
- [ ] Staging deployment verified
- [ ] Production deployment planned

---

## Quick Reference: Property Migration Map

```
Conditions (when/where restrictions apply):
├── type: RestrictionType enum (Start|Stay)
├── exactRateId: ?string
├── baseRateId: ?string
├── rateGroupId: ?string
├── resourceCategoryId: ?string
├── resourceCategoryType: ?string
├── startUtc: string (ISO 8601)
├── endUtc: string (ISO 8601)
├── days: array (weekday names)
└── hours: array (hour numbers)

Exceptions (what's restricted):
├── minAdvance: ?string (ISO 8601 duration)
├── maxAdvance: ?string (ISO 8601 duration)
├── minLength: ?string (ISO 8601 duration)
├── maxLength: ?string (ISO 8601 duration)
├── minPrice: ?array
├── maxPrice: ?array
├── minReservationCount: ?int
└── maxReservationCount: ?int
```

---

**End of Migration Guide**

# Refactoring Report: php-pms-clients v4.0.0 Integration

**Target Project:** prj-more-apartments
**Package Update:** `shelfwood/php-pms-clients` v3.x → v4.0.0
**Priority:** HIGH - Breaking Changes
**Generated:** December 23, 2025

---

## CRITICAL: Action Required

The `php-pms-clients` package has been upgraded to v4.0.0 with **BREAKING CHANGES** that require immediate code updates in the prj-more-apartments codebase.

---

## Change Summary

### Breaking Change #1: Restriction DTO Structure (HIGH IMPACT)

**Old Structure (v3.x):**
```php
$restriction->resourceCategoryId
$restriction->minLength
$restriction->type  // string
```

**New Structure (v4.0.0):**
```php
$restriction->conditions->resourceCategoryId
$restriction->exceptions->minLength
$restriction->conditions->type  // RestrictionType enum
```

### Breaking Change #2: ReservationsClient API (MEDIUM IMPACT)

**Old Signature (v3.x):**
```php
updateState(string $id, string $state)
```

**New Signature (v4.0.0):**
```php
updateState(string $id, ReservationState $state)
```

---

## Step-by-Step Refactoring Instructions

### Phase 1: Discovery (Estimated: 10 minutes)

**Objective:** Locate all code that needs updating

#### Task 1.1: Find Restriction Usage
```bash
cd /Users/shelfwood/Projects/prj-more-apartments

# Search for Restriction property access
grep -r "\$restriction->" --include="*.php" app/
grep -r "Restriction::" --include="*.php" app/

# Expected locations:
# - app/Services/BookingAvailability/
# - app/Services/Mews/
# - app/Http/Controllers/Booking/
```

#### Task 1.2: Find ReservationsClient Usage
```bash
# Search for updateState method calls
grep -r "->updateState(" --include="*.php" app/

# Search for reservation state strings
grep -r "'Confirmed'" --include="*.php" app/ | grep -i reservation
grep -r "'Canceled'" --include="*.php" app/ | grep -i reservation
```

#### Task 1.3: Document Findings
Create a list of files requiring updates with line numbers.

---

### Phase 2: Update Restriction Property Access (Estimated: 20 minutes)

**Objective:** Refactor all Restriction DTO property access

#### Task 2.1: Update Conditions Access

**Search & Replace Pattern:**
```
Find:    $restriction->resourceCategoryId
Replace: $restriction->conditions->resourceCategoryId

Find:    $restriction->exactRateId
Replace: $restriction->conditions->exactRateId

Find:    $restriction->baseRateId
Replace: $restriction->conditions->baseRateId

Find:    $restriction->type
Replace: $restriction->conditions->type

Find:    $restriction->startUtc
Replace: $restriction->conditions->startUtc

Find:    $restriction->endUtc
Replace: $restriction->conditions->endUtc

Find:    $restriction->days
Replace: $restriction->conditions->days
```

#### Task 2.2: Update Exceptions Access

**Search & Replace Pattern:**
```
Find:    $restriction->minAdvance
Replace: $restriction->exceptions->minAdvance

Find:    $restriction->maxAdvance
Replace: $restriction->exceptions->maxAdvance

Find:    $restriction->minLength
Replace: $restriction->exceptions->minLength

Find:    $restriction->maxLength
Replace: $restriction->exceptions->maxLength
```

#### Task 2.3: Update Type Comparisons

**Find:**
```php
if ($restriction->type === 'Start')
if ($restriction->type === 'Stay')
```

**Replace:**
```php
use Shelfwood\PhpPms\Mews\Enums\RestrictionType;

if ($restriction->conditions->type === RestrictionType::Start)
if ($restriction->conditions->type === RestrictionType::Stay)
```

---

### Phase 3: Update ReservationsClient Calls (Estimated: 15 minutes)

**Objective:** Convert string states to enum states

#### Task 3.1: Add Enum Import

In files using `ReservationsClient`:
```php
use Shelfwood\PhpPms\Mews\Enums\ReservationState;
```

#### Task 3.2: Update Method Calls

**String to Enum Conversion:**
```php
// OLD
$client->updateState($id, 'Confirmed');
$client->updateState($id, 'Canceled');
$client->updateState($id, 'Optional');

// NEW
$client->updateState($id, ReservationState::Confirmed);
$client->updateState($id, ReservationState::Canceled);
$client->updateState($id, ReservationState::Optional);
```

---

### Phase 4: Update Tests (Estimated: 15 minutes)

**Objective:** Ensure all tests pass with new structure

#### Task 4.1: Update Unit Tests

Files likely needing updates:
- `tests/Unit/Services/BookingAvailability/`
- `tests/Unit/Services/Mews/`

**Update Assertions:**
```php
// OLD
$this->assertEquals('Start', $restriction->type);
$this->assertEquals($categoryId, $restriction->resourceCategoryId);
$this->assertNotNull($restriction->minLength);

// NEW
use Shelfwood\PhpPms\Mews\Enums\RestrictionType;

$this->assertEquals(RestrictionType::Start, $restriction->conditions->type);
$this->assertEquals($categoryId, $restriction->conditions->resourceCategoryId);
$this->assertNotNull($restriction->exceptions->minLength);
```

#### Task 4.2: Update Integration Tests

**Update Mock Data Access:**
```php
// If you're mocking Restriction objects
$mock = Mockery::mock(Restriction::class);
$mock->conditions = new RestrictionConditions(...);
$mock->exceptions = new RestrictionExceptions(...);
```

#### Task 4.3: Run Test Suite
```bash
php artisan test --filter=Booking
php artisan test --filter=Mews
```

---

### Phase 5: Code Quality & Validation (Estimated: 10 minutes)

**Objective:** Ensure all changes are correct and complete

#### Task 5.1: Static Analysis
```bash
# Run PHPStan if configured
vendor/bin/phpstan analyse app/

# Check for undefined property access
php artisan code:analyse
```

#### Task 5.2: Manual Code Review

**Checklist:**
- [ ] All `$restriction->` access updated to use `->conditions->` or `->exceptions->`
- [ ] All string type checks replaced with enum comparisons
- [ ] All `updateState()` calls use `ReservationState` enum
- [ ] Required enum imports added
- [ ] No references to old flat structure remain

#### Task 5.3: Run Full Test Suite
```bash
php artisan test
```

---

## Expected File Changes

Based on typical Laravel application structure:

### High Probability Files
```
app/Services/BookingAvailability/MinimumStayCalculator.php
app/Services/Mews/RestrictionService.php
app/Services/Mews/ReservationService.php
app/Http/Controllers/Booking/ReservationController.php
tests/Unit/Services/BookingAvailability/MinimumStayCalculatorTest.php
tests/Feature/Booking/ReservationWorkflowTest.php
```

### Search Commands by Category

**Minimum Stay Logic:**
```bash
grep -r "minLength" app/Services/ --include="*.php"
grep -r "minAdvance" app/Services/ --include="*.php"
```

**Reservation State Management:**
```bash
grep -r "updateState" app/ --include="*.php"
grep -r "Confirmed\|Canceled\|Optional" app/Services/Mews/ --include="*.php"
```

**Restriction Filtering:**
```bash
grep -r "resourceCategoryId" app/Services/ --include="*.php"
grep -r "restriction->type" app/ --include="*.php"
```

---

## Example: Typical MinimumStayCalculator Refactoring

### BEFORE (v3.x)
```php
<?php

namespace App\Services\BookingAvailability;

class MinimumStayCalculator
{
    public function calculateForDate(Carbon $date, string $categoryId, array $restrictions): ?string
    {
        foreach ($restrictions as $restriction) {
            // Check category match
            if ($restriction->resourceCategoryId !== $categoryId) {
                continue;
            }

            // Check date in range
            $start = Carbon::parse($restriction->startUtc);
            $end = Carbon::parse($restriction->endUtc);

            if ($date->between($start, $end)) {
                // Check restriction type
                if ($restriction->type === 'Start') {
                    return $restriction->minLength;
                }
            }
        }

        return null;
    }
}
```

### AFTER (v4.0.0)
```php
<?php

namespace App\Services\BookingAvailability;

use Shelfwood\PhpPms\Mews\Enums\RestrictionType;

class MinimumStayCalculator
{
    public function calculateForDate(Carbon $date, string $categoryId, array $restrictions): ?string
    {
        foreach ($restrictions as $restriction) {
            // Check category match
            if ($restriction->conditions->resourceCategoryId !== $categoryId) {
                continue;
            }

            // Check date in range
            $start = Carbon::parse($restriction->conditions->startUtc);
            $end = Carbon::parse($restriction->conditions->endUtc);

            if ($date->between($start, $end)) {
                // Check restriction type
                if ($restriction->conditions->type === RestrictionType::Start) {
                    return $restriction->exceptions->minLength;
                }
            }
        }

        return null;
    }
}
```

**Changes Made:**
1. Added `use Shelfwood\PhpPms\Mews\Enums\RestrictionType;`
2. `$restriction->resourceCategoryId` → `$restriction->conditions->resourceCategoryId`
3. `$restriction->startUtc` → `$restriction->conditions->startUtc`
4. `$restriction->endUtc` → `$restriction->conditions->endUtc`
5. `$restriction->type === 'Start'` → `$restriction->conditions->type === RestrictionType::Start`
6. `$restriction->minLength` → `$restriction->exceptions->minLength`

---

## Deployment Strategy

### Local Development
1. ✅ Update composer dependency: `composer require shelfwood/php-pms-clients:^4.0`
2. ✅ Apply all refactoring changes
3. ✅ Run test suite: `php artisan test`
4. ✅ Manual testing of booking flow
5. ✅ Commit changes

### Staging Deployment
1. Deploy to staging environment
2. Run automated tests
3. Perform manual QA:
   - Create test reservation
   - Update reservation state
   - Check minimum stay calculations
   - Verify restriction filtering
4. Monitor logs for errors

### Production Deployment
1. Schedule deployment during low-traffic window
2. Deploy code updates
3. Monitor application logs
4. Monitor error tracking (Sentry/Bugsnag)
5. Verify key flows:
   - New bookings
   - Booking modifications
   - Availability checks
6. Rollback plan: Revert to `^3.8` if critical issues

---

## Troubleshooting

### Common Error: "Attempt to read property on null"

**Symptom:**
```
Attempt to read property "resourceCategoryId" on null
```

**Cause:** Forgot to update property path

**Fix:**
```php
// Check for null before accessing
if ($restriction->conditions !== null) {
    $categoryId = $restriction->conditions->resourceCategoryId;
}
```

### Common Error: "Type error: string given, ReservationState expected"

**Symptom:**
```
TypeError: Argument #2 ($newState) must be of type ReservationState, string given
```

**Cause:** Passing string instead of enum to updateState()

**Fix:**
```php
use Shelfwood\PhpPms\Mews\Enums\ReservationState;

// Change from:
$client->updateState($id, 'Confirmed');

// To:
$client->updateState($id, ReservationState::Confirmed);
```

### Common Error: "Undefined property: conditions"

**Symptom:**
```
Undefined property: Restriction::$conditions
```

**Cause:** Old version of package still cached

**Fix:**
```bash
composer dump-autoload
php artisan cache:clear
php artisan config:clear
```

---

## Success Criteria

- [ ] All tests passing (100% pass rate)
- [ ] No PHPStan/static analysis errors
- [ ] Booking flow works end-to-end
- [ ] Minimum stay calculations accurate
- [ ] Reservation state updates successful
- [ ] No errors in staging logs
- [ ] Code review approved
- [ ] Production deployment successful

---

## Estimated Total Time

| Phase | Duration |
|-------|----------|
| Discovery | 10 min |
| Restriction refactoring | 20 min |
| ReservationsClient updates | 15 min |
| Test updates | 15 min |
| Validation | 10 min |
| **Total** | **70 minutes** |

Additional buffer: 20 minutes for unexpected issues

**Total Estimated Time:** 90 minutes (1.5 hours)

---

## Resources

- **Full Migration Guide:** `/MIGRATION_v4.0.0.md`
- **Package Repository:** https://github.com/j-shelfwood/php-pms-clients
- **Release Tag:** v4.0.0
- **Commit Hash:** bee92e5

---

## Contact & Support

For questions or issues during refactoring:
1. Check `/MIGRATION_v4.0.0.md` for detailed examples
2. Review package tests: `vendor/shelfwood/php-pms-clients/tests/`
3. Open GitHub issue if blocking problems occur

---

**End of Refactoring Report**

*Ready to proceed with systematic refactoring of prj-more-apartments codebase.*

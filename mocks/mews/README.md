# Mews API Test Fixtures

This directory contains realistic Mews API response fixtures and webhook payloads for testing applications that integrate with Mews PMS.

## Purpose

These fixtures provide:
- **Consistent test data** across all consuming applications
- **Realistic API structures** based on actual Mews API responses
- **UUID consistency** - All fixtures reference the same test entities
- **Webhook payloads** for all 7 Mews event types

## Directory Structure

```
mocks/mews/
├── README.md              # This file
├── webhooks/              # Webhook payload fixtures (7 files)
│   ├── service-order-updated.json     # Reservation webhook
│   ├── resource-updated.json          # Property/resource webhook
│   ├── resource-block-updated.json    # Calendar block webhook
│   ├── message-added.json             # Message webhook
│   ├── customer-updated.json          # Customer webhook
│   ├── batch-webhook.json             # Multi-event webhook
│   └── malformed-webhook.json         # Error handling test
├── requests/              # Request payload examples (10 files)
│   └── ...
└── responses/             # API response fixtures (18 files)
    ├── agecategories-getall.json
    ├── customers-add.json
    ├── customers-search.json
    ├── customer-getbyid.json          # Single customer (NEW)
    ├── rates-getall.json
    ├── rates-getpricing.json
    ├── reservations-add.json
    ├── reservations-getall.json
    ├── reservation-getbyid.json       # Single reservation (NEW)
    ├── resourcecategories-getall.json
    ├── resourcecategoryassignments-getall.json
    ├── resourcecategoryassignment-getforresource.json  # Single assignment (NEW)
    ├── resources-getall.json
    ├── resource-getbyid.json          # Single resource (NEW)
    ├── restrictions-getall.json
    ├── services-getall.json
    ├── services-getavailability.json
    └── service-getbyid.json           # Single service (NEW)
```

## Usage

### Using MewsFixtures Helper (Recommended)

```php
use Shelfwood\PhpPms\Tests\Support\MewsFixtures;

// Load webhook payload
$payload = MewsFixtures::webhookPayload('service-order-updated');

// Load API response
$response = MewsFixtures::apiResponse('reservation-getbyid');

// Generate webhook signature
$signature = MewsFixtures::generateSignature($payload, 'webhook-secret');

// Verify webhook signature
$isValid = MewsFixtures::verifySignature($payload, $signature, 'webhook-secret');

// Use consistent test UUIDs
$enterpriseId = MewsFixtures::ENTERPRISE_ID;
$resourceId = MewsFixtures::RESOURCE_ID;
```

### Direct File Loading (Legacy)

```php
// Load mock response as array
$mockData = json_decode(
    file_get_contents(__DIR__ . '/../../mocks/mews/responses/services-getall.json'),
    true
);

// Use in your test setup
$service = $mockData['Services'][0];
expect($service['Id'])->toBe('bd26d8db-86a4-4f18-9e94-1b2362a1073c');
```

## UUID Consistency Map

All fixtures reference the same test entities to enable relational testing:

| Entity | UUID | MewsFixtures Constant | Used In |
|--------|------|-----------------------|---------|
| Enterprise | `851df8c8-90f2-4c4a-8e01-a4fc46b25178` | `ENTERPRISE_ID` | All fixtures |
| Integration | `c8bee838-7fb1-4f4e-8fac-ac87008b2f90` | `INTEGRATION_ID` | Webhook payloads |
| Service | `bd26d8db-86a4-4f18-9e94-1b2362a1073c` | `SERVICE_ID` | resource-getbyid, service-getbyid, reservation-getbyid |
| Resource | `095a6d7f-4893-4a3b-9c35-ff595d4bfa0c` | `RESOURCE_ID` | resource-*, reservation-getbyid, webhooks |
| Category | `773d5e42-de1e-43a0-9ce6-c3e7511c1e0a` | `CATEGORY_ID` | resourcecategoryassignment-*, reservation-getbyid |
| Reservation | `bfee2c44-1f84-4326-a862-5289598f6e2d` | `RESERVATION_ID` | reservation-*, service-order-updated webhook |
| Customer | `c2f1d888-232e-49eb-87ac-5f75363af13b` | `CUSTOMER_ID` | customer-*, reservation-getbyid |
| Block | `7cccbdc6-73cf-4cd4-8056-6fd00f4d9699` | `BLOCK_ID` | resource-block-updated webhook |
| Message | `a1234567-89ab-cdef-0123-456789abcdef` | `MESSAGE_ID` | message-added webhook |
| Rate | `ed4b660b-19d0-434b-9360-a4de2101ed08` | `RATE_ID` | reservation-*, rates-* |
| Age Category (Adult) | `1f67644f-052d-4863-acdf-ae1600c60ca0` | `AGE_CATEGORY_ADULT_ID` | agecategories-*, reservations |

## Available Mocks

### Services API
- `services-getall` - Retrieve all services/properties
- `services-getavailability` - Get availability for date range
- `service-getbyid` - Get single service by ID (**NEW**)

### Customers API
- `customers-search` - Search customers by email
- `customers-add` - Create new customer record
- `customer-getbyid` - Get single customer by ID (**NEW**)

### Reservations API
- `reservations-add` - Create new reservation
- `reservations-getall` - Retrieve reservations with filters
- `reservation-getbyid` - Get single reservation by ID (**NEW**)

### Rates API
- `rates-getall` - Get all rate plans
- `rates-getpricing` - Get pricing for date range

### Resources API
- `resources-getall` - Get all physical units/rooms
- `resource-getbyid` - Get single resource by ID (**NEW**)

### Resource Categories API
- `resourcecategories-getall` - Get room types/categories

### Resource Category Assignments API
- `resourcecategoryassignments-getall` - Get resource-to-category mappings
- `resourcecategoryassignment-getforresource` - Get assignment for single resource (**NEW**)

### Age Categories API
- `agecategories-getall` - Get guest classification categories (Adult/Child)

### Restrictions API
- `restrictions-getall` - Get booking restrictions (minimum stay, etc.)

### Webhooks (**NEW**)
- `service-order-updated` - Reservation webhook (created/updated/cancelled)
- `resource-updated` - Property/resource change webhook
- `resource-block-updated` - Calendar block webhook
- `message-added` - Guest message webhook
- `customer-updated` - Customer change webhook
- `batch-webhook` - Multi-event webhook (3 events)
- `malformed-webhook` - Missing Value key (error handling test)

## Webhook Event Types

All 7 Mews webhook event types are represented:

1. **ServiceOrderUpdated** - Reservation changes (created, updated, cancelled)
2. **ResourceUpdated** - Property/resource changes
3. **ResourceBlockUpdated** - Calendar availability blocks
4. **MessageAdded** - Guest messages
5. **CustomerAdded** - New customers
6. **CustomerUpdated** - Customer updates
7. **PaymentUpdated** - Payment status changes

## Webhook Payload Structure

All webhooks follow this structure:

```json
{
  "EnterpriseId": "851df8c8-90f2-4c4a-8e01-a4fc46b25178",
  "IntegrationId": "c8bee838-7fb1-4f4e-8fac-ac87008b2f90",
  "Events": [
    {
      "Discriminator": "ServiceOrderUpdated",
      "Value": {
        "Id": "bfee2c44-1f84-4326-a862-5289598f6e2d"
      }
    }
  ]
}
```

**Important**: Webhooks only send entity IDs. Full entity data must be fetched via corresponding API endpoints.

## Example: Testing Webhooks

```php
use Shelfwood\PhpPms\Tests\Support\MewsFixtures;

it('processes Mews reservation webhook', function () {
    // Load webhook payload
    $payload = MewsFixtures::webhookPayload('service-order-updated');
    $signature = MewsFixtures::generateSignature($payload, 'webhook-secret');

    // Mock API client responses for entity fetching
    Http::fake([
        '*/reservations/get' => Http::response(
            MewsFixtures::apiResponse('reservation-getbyid')
        )
    ]);

    // Post webhook to application
    $this->postJson('/api/webhooks/pms/mews', $payload, [
        'X-Mews-Signature' => $signature
    ])->assertOk();

    // Verify booking created
    expect(Booking::where('external_id', MewsFixtures::RESERVATION_ID)->exists())
        ->toBeTrue();
});
```

## Mock Data Characteristics

### Realistic Data
- Proper ISO 8601 datetime formats
- Valid currency codes (EUR)
- Localized text objects (en-US, es-ES, nl-NL)
- Realistic pricing with tax breakdowns
- Complete nested structures

### UUID Format
All UUIDs follow RFC 4122 format: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`

## Adding New Fixtures

When adding new fixtures:

1. **Use consistent UUIDs** - Reference existing entities from UUID Consistency Map
2. **Base on real API responses** - Capture from Mews API documentation or live calls
3. **Add to MewsFixtures constants** - If introducing new entities
4. **Update this README** - Document the new fixture in the appropriate section
5. **Add tests** - Verify fixture loads and structure in `MewsFixturesTest.php`

## Maintenance

**Source**: Based on Mews Connector API documentation (https://mews-systems.gitbook.io/connector-api)

**Last Updated**: 2025-12-22

**API Version**: v1 (current)

**Changes in Latest Update**:
- Added 7 webhook payload fixtures
- Added 5 single-entity getter response mocks
- Created MewsFixtures utility class (`tests/Support/MewsFixtures.php`)
- Standardized UUID consistency across all fixtures

## Related Documentation

- **MewsFixtures Class**: `tests/Support/MewsFixtures.php` - Fixture loader utility
- **MewsFixtures Tests**: `tests/Support/MewsFixturesTest.php` - Fixture validation tests
- **Mews API Docs**: https://mews-systems.gitbook.io/connector-api
- **Webhook Docs**: https://mews-systems.gitbook.io/connector-api/events/wh-general

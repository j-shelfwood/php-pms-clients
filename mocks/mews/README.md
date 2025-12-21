# Mews API Mock Data

This directory contains mock request/response pairs for all Mews Connector API endpoints.

## Structure

```
mocks/mews/
├── requests/           # Example request payloads
└── responses/          # Example API responses
```

## Available Mocks

### Services API
- `services-getall` - Retrieve all services/properties
- `services-getavailability` - Get availability for date range

### Customers API
- `customers-search` - Search customers by email
- `customers-add` - Create new customer record

### Reservations API
- `reservations-add` - Create new reservation
- `reservations-getall` - Retrieve reservations with filters

### Rates API
- `rates-getall` - Get all rate plans
- `rates-getpricing` - Get pricing for date range

### Resources API
- `resources-getall` - Get all physical units/rooms

### Resource Categories API
- `resourcecategories-getall` - Get room types/categories

### Resource Category Assignments API
- `resourcecategoryassignments-getall` - Get resource-to-category mappings

### Age Categories API
- `agecategories-getall` - Get guest classification categories (Adult/Child)

### Restrictions API
- `restrictions-getall` - Get booking restrictions (minimum stay, etc.)

## Usage

### In Tests

```php
use function Tests\Support\loadMock;

it('creates a reservation', function () {
    $requestPayload = loadMock('mews/requests/reservations-add.json');
    $expectedResponse = loadMock('mews/responses/reservations-add.json');

    $mockHttp = Mockery::mock(HttpClient::class);
    $mockHttp->shouldReceive('post')
        ->with('/api/connector/v1/reservations/add', $requestPayload)
        ->andReturn($expectedResponse);

    $client = new MewsConnectorAPI($config, $mockHttp);
    $response = $client->createReservation(...);

    expect($response['Reservations'][0]['Reservation']['Id'])
        ->toBe('bfee2c44-1f84-4326-a862-5289598a6cea');
});
```

### Generating Test Data

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

## Mock Data Characteristics

### Consistent UUIDs
All mocks use consistent UUIDs for cross-referencing:

- **Service ID:** `bd26d8db-86a4-4f18-9e94-1b2362a1073c`
- **Enterprise ID:** `3edbe1b4-6739-40b7-81b3-82bca5a2d610`
- **Standard Room Category ID:** `773d5e42-de1e-43a0-9ce6-c3e7511c1e0a`
- **Deluxe Suite Category ID:** `a9f42c86-1e95-4e3b-a7c9-82bca5a2d610`
- **Adult Age Category ID:** `1f67644f-052d-4863-acdf-ae1600c60ca0`
- **Child Age Category ID:** `55a76d26-0df3-4efb-b0cc-ade3011a53a5`
- **Customer ID (John Doe):** `35d4b117-4e60-44a3-9580-c1deae0557c1`
- **Customer ID (Jane Smith):** `8a3c8f42-1e95-4e3b-a7c9-82bca5a2d610`
- **Rate ID (BAR):** `ed4b660b-19d0-434b-9360-a4de2101ed08`

### Realistic Data
- Proper ISO 8601 datetime formats
- Valid currency codes (EUR)
- Localized text objects (en-US)
- Realistic pricing with tax breakdowns
- Complete nested structures

## Updating Mocks

When the Mews API changes:

1. Update response structure in `/mocks/mews/responses/`
2. Update corresponding request in `/mocks/mews/requests/`
3. Update documentation in `/docs/mews/endpoints/`
4. Run tests to verify compatibility

## See Also

- [Mews API Documentation](../docs/mews/README.md)
- [Endpoint Documentation](../docs/mews/endpoints/)
- [Mews GitBook](https://mews-systems.gitbook.io/connector-api)

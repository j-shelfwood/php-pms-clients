<?php

namespace Shelfwood\PhpPms\Mews\Clients\Production;

use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Payloads\SearchCustomersPayload;
use Shelfwood\PhpPms\Mews\Payloads\CreateCustomerPayload;
use Shelfwood\PhpPms\Mews\Responses\CustomersResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Customer;
use Shelfwood\PhpPms\Mews\Exceptions\MewsApiException;

class CustomersClient
{
    public function __construct(
        private MewsHttpClient $httpClient
    ) {}

    /**
     * Find or create a customer in Mews
     *
     * @param CreateCustomerPayload $createPayload Customer creation payload
     * @return string Customer UUID
     * @throws MewsApiException
     */
    public function findOrCreate(CreateCustomerPayload $createPayload): string
    {
        // First, search for existing customer by email (if provided)
        if ($createPayload->email !== null) {
            $searchPayload = new SearchCustomersPayload(emails: [$createPayload->email]);
            $existing = $this->search($searchPayload);

            if ($existing->items->isNotEmpty()) {
                return $existing->items->first()->id;
            }
        }

        // Create new customer if not found
        $body = $this->httpClient->buildRequestBody($createPayload->toArray());

        $response = $this->httpClient->post('/api/connector/v1/customers/add', $body);

        // /customers/add returns a single customer object directly, not wrapped in Customers array
        // This differs from /customers/getAll which returns { "Customers": [...] }
        if (!isset($response['Id'])) {
            throw new MewsApiException('Failed to create customer: Invalid API response', 500);
        }

        $customer = Customer::map($response);

        return $customer->id;
    }

    /**
     * Search for customers by email
     *
     * @param SearchCustomersPayload $payload Search payload
     * @return CustomersResponse Array of matching customer objects
     * @throws MewsApiException
     */
    public function search(SearchCustomersPayload $payload): CustomersResponse
    {
        $allCustomers = [];
        $cursor = $payload->cursor;

        do {
            $pagePayload = new SearchCustomersPayload(
                emails: $payload->emails,
                extent: $payload->extent,
                limitCount: $payload->limitCount,
                cursor: $cursor
            );

            $body = $this->httpClient->buildRequestBody($pagePayload->toArray());

            $response = $this->httpClient->post('/api/connector/v1/customers/getAll', $body);

            $pageResponse = CustomersResponse::map($response);
            $allCustomers = array_merge($allCustomers, $pageResponse->items->all());
            $cursor = $pageResponse->cursor;
        } while ($cursor !== null);

        return new CustomersResponse(items: collect($allCustomers));
    }

    /**
     * Get customer by ID
     *
     * @param string $customerId Customer UUID
     * @return Customer Customer object
     * @throws MewsApiException
     */
    public function getById(string $customerId): Customer
    {
        $body = $this->httpClient->buildRequestBody([
            'CustomerIds' => [$customerId],
            'Extent' => [
                'Customers' => true,
                'Documents' => false,
                'Addresses' => false,
            ],
            'Limitation' => [
                'Count' => 100,
            ],
        ]);

        $response = $this->httpClient->post('/api/connector/v1/customers/getAll', $body);

        $customersResponse = CustomersResponse::map($response);

        if ($customersResponse->items->isEmpty()) {
            throw new MewsApiException("Customer not found: {$customerId}", 404);
        }

        return $customersResponse->items->first();
    }
}

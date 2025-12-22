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
        // First, search for existing customer by email
        $searchPayload = new SearchCustomersPayload(emails: [$createPayload->email]);
        $existing = $this->search($searchPayload);

        if (count($existing->items) > 0) {
            return $existing->items[0]->id;
        }

        // Create new customer if not found
        $body = $this->httpClient->buildRequestBody($createPayload->toArray());

        $response = $this->httpClient->post('/api/connector/v1/customers/add', $body);

        $customersResponse = CustomersResponse::map($response);

        if (count($customersResponse->items) === 0) {
            throw new MewsApiException('Failed to create customer', 500);
        }

        return $customersResponse->items[0]->id;
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
        $body = $this->httpClient->buildRequestBody($payload->toArray());

        $response = $this->httpClient->post('/api/connector/v1/customers/search', $body);

        return CustomersResponse::map($response);
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
        ]);

        $response = $this->httpClient->post('/api/connector/v1/customers/getAll', $body);

        $customersResponse = CustomersResponse::map($response);

        if (count($customersResponse->items) === 0) {
            throw new MewsApiException("Customer not found: {$customerId}", 404);
        }

        return $customersResponse->items[0];
    }
}

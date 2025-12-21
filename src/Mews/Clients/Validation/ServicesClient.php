<?php

namespace Shelfwood\PhpPms\Mews\Clients\Validation;

use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Exceptions\MewsApiException;
use Shelfwood\PhpPms\Mews\Responses\ServicesResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Service;

class ServicesClient
{
    public function __construct(
        private MewsHttpClient $httpClient
    ) {}

    public function getAll(?array $serviceIds = null): ServicesResponse
    {
        $body = $this->httpClient->buildRequestBody([
            'ServiceIds' => $serviceIds,
        ]);

        $response = $this->httpClient->post('/api/connector/v1/services/getAll', $body);

        return ServicesResponse::map($response);
    }

    public function getById(string $serviceId): Service
    {
        $servicesResponse = $this->getAll([$serviceId]);

        if (empty($servicesResponse->items)) {
            throw new MewsApiException("Service not found: {$serviceId}", 404);
        }

        return $servicesResponse->items[0];
    }
}

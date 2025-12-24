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
        $allServices = [];
        $cursor = null;

        do {
            $body = $this->httpClient->buildRequestBody([
                'ServiceIds' => $serviceIds,
                'Limitation' => [
                    'Count' => 1000,
                    ...($cursor !== null ? ['Cursor' => $cursor] : []),
                ],
            ]);

            $response = $this->httpClient->post('/api/connector/v1/services/getAll', $body);
            $pageResponse = ServicesResponse::map($response);

            $allServices = array_merge($allServices, $pageResponse->items->all());
            $cursor = $pageResponse->cursor;
        } while ($cursor !== null);

        return new ServicesResponse(items: collect($allServices));
    }

    public function getById(string $serviceId): Service
    {
        $servicesResponse = $this->getAll([$serviceId]);

        if ($servicesResponse->items->isEmpty()) {
            throw new MewsApiException("Service not found: {$serviceId}", 404);
        }

        return $servicesResponse->items->first();
    }
}

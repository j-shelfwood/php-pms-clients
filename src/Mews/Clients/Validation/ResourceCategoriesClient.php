<?php

namespace Shelfwood\PhpPms\Mews\Clients\Validation;

use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Responses\ResourceCategoriesResponse;

class ResourceCategoriesClient
{
    public function __construct(
        private MewsHttpClient $httpClient
    ) {}

    public function getForService(string $serviceId): ResourceCategoriesResponse
    {
        $body = $this->httpClient->buildRequestBody([
            'ServiceIds' => [$serviceId],
            'Limitation' => ['Count' => 1000],
        ]);

        $response = $this->httpClient->post('/api/connector/v1/resourceCategories/getAll', $body);

        return ResourceCategoriesResponse::map($response);
    }
}

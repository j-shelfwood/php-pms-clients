<?php

namespace Shelfwood\PhpPms\Mews\Clients\Validation;

use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Exceptions\MewsApiException;
use Shelfwood\PhpPms\Mews\Responses\ResourcesResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\Resource;

class ResourcesClient
{
    public function __construct(
        private MewsHttpClient $httpClient
    ) {}

    public function getAll(
        ?array $resourceCategoryIds = null,
        ?array $resourceIds = null
    ): ResourcesResponse {
        $params = [];

        if ($resourceCategoryIds !== null) {
            $params['ResourceCategoryIds'] = $resourceCategoryIds;
        }

        if ($resourceIds !== null) {
            $params['ResourceIds'] = $resourceIds;
        }

        $body = $this->httpClient->buildRequestBody($params);

        $response = $this->httpClient->post('/api/connector/v1/resources/getAll', $body);

        return ResourcesResponse::map($response);
    }

    public function getForCategory(string $categoryId): ResourcesResponse
    {
        return $this->getAll(resourceCategoryIds: [$categoryId]);
    }

    public function getById(string $resourceId): Resource
    {
        $resourcesResponse = $this->getAll(resourceIds: [$resourceId]);

        if ($resourcesResponse->items->isEmpty()) {
            throw new MewsApiException("Resource not found: {$resourceId}", 404);
        }

        return $resourcesResponse->items->first();
    }
}

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
        $allCategories = [];
        $cursor = null;

        do {
            $body = $this->httpClient->buildRequestBody([
                'ServiceIds' => [$serviceId],
                'Limitation' => [
                    'Count' => 1000,
                    ...($cursor !== null ? ['Cursor' => $cursor] : []),
                ],
            ]);

            $response = $this->httpClient->post('/api/connector/v1/resourceCategories/getAll', $body);
            $pageResponse = ResourceCategoriesResponse::map($response);

            $allCategories = array_merge($allCategories, $pageResponse->items->all());
            $cursor = $pageResponse->cursor;
        } while ($cursor !== null);

        return new ResourceCategoriesResponse(items: collect($allCategories));
    }
}

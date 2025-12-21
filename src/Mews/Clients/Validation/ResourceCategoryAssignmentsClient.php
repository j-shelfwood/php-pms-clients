<?php

namespace Shelfwood\PhpPms\Mews\Clients\Validation;

use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Responses\ResourceCategoryAssignmentsResponse;

class ResourceCategoryAssignmentsClient
{
    public function __construct(
        private MewsHttpClient $httpClient
    ) {}

    public function getAll(
        ?array $resourceCategoryIds = null,
        ?array $resourceIds = null,
        ?array $activityStates = null,
        ?int $limitCount = 1000
    ): ResourceCategoryAssignmentsResponse {
        $params = [
            'Limitation' => [
                'Count' => min(max($limitCount ?? 1000, 1), 1000),
            ],
        ];

        if ($resourceCategoryIds !== null) {
            $params['ResourceCategoryIds'] = $resourceCategoryIds;
        }

        if ($resourceIds !== null) {
            $params['ResourceIds'] = $resourceIds;
        }

        if ($activityStates !== null) {
            $params['ActivityStates'] = $activityStates;
        }

        $body = $this->httpClient->buildRequestBody($params);

        $response = $this->httpClient->post('/api/connector/v1/resourceCategoryAssignments/getAll', $body);

        return ResourceCategoryAssignmentsResponse::map($response);
    }
}

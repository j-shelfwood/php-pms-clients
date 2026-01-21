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
        $allAssignments = [];
        $cursor = null;

        if ($resourceIds !== null && ($resourceCategoryIds === null || $resourceCategoryIds === [])) {
            throw new \InvalidArgumentException('ResourceCategoryIds is required when filtering by ResourceIds');
        }

        do {
            $params = [
                'Limitation' => [
                    'Count' => min(max($limitCount ?? 1000, 1), 1000),
                    ...($cursor !== null ? ['Cursor' => $cursor] : []),
                ],
            ];

            $params['ResourceCategoryIds'] = $resourceCategoryIds ?? [];

            if ($resourceIds !== null) {
                $params['ResourceIds'] = $resourceIds;
            }

            if ($activityStates !== null) {
                $params['ActivityStates'] = $activityStates;
            }

            $body = $this->httpClient->buildRequestBody($params);

            $response = $this->httpClient->post('/api/connector/v1/resourceCategoryAssignments/getAll', $body);
            $pageResponse = ResourceCategoryAssignmentsResponse::map($response);

            $allAssignments = array_merge($allAssignments, $pageResponse->items->all());
            $cursor = $pageResponse->cursor;
        } while ($cursor !== null);

        return new ResourceCategoryAssignmentsResponse(items: collect($allAssignments));
    }


    public function getForResource(string $resourceId): ?\Shelfwood\PhpPms\Mews\Responses\ValueObjects\ResourceCategoryAssignment
    {
        $response = $this->getAll(resourceIds: [$resourceId]);
        return $response->items->first();
    }

}

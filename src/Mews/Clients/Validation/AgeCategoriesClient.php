<?php

namespace Shelfwood\PhpPms\Mews\Clients\Validation;

use Shelfwood\PhpPms\Mews\Enums\AgeClassification;
use Shelfwood\PhpPms\Mews\Http\MewsHttpClient;
use Shelfwood\PhpPms\Mews\Exceptions\MewsApiException;
use Shelfwood\PhpPms\Mews\Responses\AgeCategoriesResponse;
use Shelfwood\PhpPms\Mews\Responses\ValueObjects\AgeCategory;

class AgeCategoriesClient
{
    public function __construct(
        private MewsHttpClient $httpClient
    ) {}

    public function getAll(string $serviceId): AgeCategoriesResponse
    {
        $allCategories = [];
        $cursor = null;

        do {
            $body = $this->httpClient->buildRequestBody([
                'ServiceIds' => [$serviceId],
                'Limitation' => [
                    'Count' => 100,
                    ...($cursor !== null ? ['Cursor' => $cursor] : []),
                ],
            ]);

            $response = $this->httpClient->post('/api/connector/v1/ageCategories/getAll', $body);
            $pageResponse = AgeCategoriesResponse::map($response);

            $allCategories = array_merge($allCategories, $pageResponse->items->all());
            $cursor = $pageResponse->cursor;
        } while ($cursor !== null);

        return new AgeCategoriesResponse(items: collect($allCategories));
    }

    public function getAdultCategory(string $serviceId): ?AgeCategory
    {
        $categoriesResponse = $this->getAll($serviceId);

        foreach ($categoriesResponse->items as $category) {
            if ($category->classification === AgeClassification::Adult && $category->isActive) {
                return $category;
            }
        }

        return null;
    }

    public function getChildCategory(string $serviceId): ?AgeCategory
    {
        $categoriesResponse = $this->getAll($serviceId);

        foreach ($categoriesResponse->items as $category) {
            if ($category->classification === AgeClassification::Child && $category->isActive) {
                return $category;
            }
        }

        return null;
    }
}

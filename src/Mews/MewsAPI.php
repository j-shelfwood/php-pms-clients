<?php

namespace Shelfwood\PhpPms\Clients\Mews;

class MewsAPI
{
    protected string $baseUrl;

    protected string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }
}

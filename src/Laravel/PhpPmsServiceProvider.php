<?php

namespace Shelfwood\PhpPms\Laravel;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;

class PhpPmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerBookingManagerAPI();
    }

    protected function registerBookingManagerAPI(): void
    {
        $this->app->bind(BookingManagerAPI::class, function ($app, $parameters) {
            $baseUrl = $parameters['base_url'] ?? 'https://xml.billypds.com';
            $apiKey = $parameters['api_key'] ?? 'dummy-key';
            $httpClient = $parameters['http_client'] ?? new Client(); // Simple, clean client
            $logger = $parameters['logger'] ?? $app->make(LoggerInterface::class);

            return new BookingManagerAPI($baseUrl, $apiKey, $httpClient, $logger);
        });
    }
}
<?php

namespace Shelfwood\PhpPms\Laravel;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Shelfwood\PhpPms\BookingManager\BookingManagerAPI;

class PhpPmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/php-pms.php', 'php-pms');

        $this->registerHttpClient();
        $this->registerBookingManagerAPI();
    }

    public function boot(): void
    {
        $this->publishConfig();
    }

    protected function registerHttpClient(): void
    {
        $this->app->bind('php-pms.http-client', function ($app) {
            $config = $app['config']['php-pms.http'];

            $options = [
                'timeout' => $config['timeout'] ?? 30,
                'verify' => $config['verify_ssl'] ?? true,
            ];

            // Only enable debug in local environment if explicitly enabled
            if ($app->environment('local') && ($config['debug'] ?? false)) {
                $options['debug'] = true;
            }

            // Explicitly disable debug output in production environments
            if ($app->environment(['production', 'staging'])) {
                $options['debug'] = false;
            }

            return new Client($options);
        });
    }

    protected function registerBookingManagerAPI(): void
    {
        $this->app->bind(BookingManagerAPI::class, function ($app, $parameters) {
            $baseUrl = $parameters['base_url'] ?? 'https://xml.billypds.com';
            $apiKey = $parameters['api_key'] ?? 'dummy-key';
            $httpClient = $parameters['http_client'] ?? $app->make('php-pms.http-client');
            $logger = $parameters['logger'] ?? $app->make(LoggerInterface::class);

            return new BookingManagerAPI($baseUrl, $apiKey, $httpClient, $logger);
        });
    }

    protected function publishConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/php-pms.php' => config_path('php-pms.php'),
        ], 'php-pms-config');
    }
}
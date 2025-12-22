<?php

use Shelfwood\PhpPms\Mews\Enums\WebhookEventType;

it('has all webhook event types', function () {
    expect(WebhookEventType::cases())->toHaveCount(7)
        ->and(WebhookEventType::ServiceOrderUpdated->value)->toBe('ServiceOrderUpdated')
        ->and(WebhookEventType::ResourceUpdated->value)->toBe('ResourceUpdated')
        ->and(WebhookEventType::MessageAdded->value)->toBe('MessageAdded')
        ->and(WebhookEventType::ResourceBlockUpdated->value)->toBe('ResourceBlockUpdated')
        ->and(WebhookEventType::CustomerAdded->value)->toBe('CustomerAdded')
        ->and(WebhookEventType::CustomerUpdated->value)->toBe('CustomerUpdated')
        ->and(WebhookEventType::PaymentUpdated->value)->toBe('PaymentUpdated');
});

it('can be created from string', function () {
    $eventType = WebhookEventType::from('ServiceOrderUpdated');
    expect($eventType)->toBe(WebhookEventType::ServiceOrderUpdated);
});

it('supports tryFrom for safe parsing', function () {
    $eventType = WebhookEventType::tryFrom('ServiceOrderUpdated');
    expect($eventType)->toBe(WebhookEventType::ServiceOrderUpdated);
});

it('returns null for invalid discriminator via tryFrom', function () {
    $eventType = WebhookEventType::tryFrom('InvalidEventType');
    expect($eventType)->toBeNull();
});

it('throws on invalid value via from', function () {
    WebhookEventType::from('InvalidEventType');
})->throws(\ValueError::class);

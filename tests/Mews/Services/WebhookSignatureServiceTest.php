<?php

use Shelfwood\PhpPms\Mews\Services\WebhookSignatureService;

it('generates a deterministic signature for payload and secret', function () {
    $payload = ['foo' => 'bar', 'n' => 1];
    $secret = 'test-secret';

    $sig1 = WebhookSignatureService::generate($payload, $secret);
    $sig2 = WebhookSignatureService::generate($payload, $secret);

    expect($sig1)->toBeString()
        ->and($sig1)->toBe($sig2);
});

it('verifies signatures correctly', function () {
    $payload = ['foo' => 'bar'];
    $secret = 'test-secret';
    $signature = WebhookSignatureService::generate($payload, $secret);

    expect(WebhookSignatureService::verify($payload, $signature, $secret))->toBeTrue()
        ->and(WebhookSignatureService::verify($payload, 'invalid', $secret))->toBeFalse();
});

it('verifySafe returns false for null or empty signature', function () {
    $payload = ['foo' => 'bar'];
    $secret = 'test-secret';

    expect(WebhookSignatureService::verifySafe($payload, null, $secret))->toBeFalse()
        ->and(WebhookSignatureService::verifySafe($payload, '', $secret))->toBeFalse();
});


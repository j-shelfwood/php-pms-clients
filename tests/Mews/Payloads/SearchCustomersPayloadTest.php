<?php

use Shelfwood\PhpPms\Mews\Payloads\SearchCustomersPayload;

it('creates payload with email array', function () {
    $payload = new SearchCustomersPayload(emails: ['john@example.com', 'jane@example.com']);

    expect($payload->emails)->toBe(['john@example.com', 'jane@example.com']);
});

it('converts to array with Emails key', function () {
    $payload = new SearchCustomersPayload(emails: ['test@example.com']);

    $array = $payload->toArray();

    expect($array)->toHaveKey('Emails')
        ->and($array['Emails'])->toBe(['test@example.com']);
});

it('throws exception when emails array is empty', function () {
    new SearchCustomersPayload(emails: []);
})->throws(\InvalidArgumentException::class, 'Emails cannot be empty');

it('throws exception for invalid email in array', function () {
    new SearchCustomersPayload(emails: ['valid@example.com', 'invalid-email']);
})->throws(\InvalidArgumentException::class, 'Invalid email format: invalid-email');

it('validates all emails in array', function () {
    $payload = new SearchCustomersPayload(emails: [
        'user1@example.com',
        'user2@example.com',
        'user3@example.com'
    ]);

    expect($payload->emails)->toHaveCount(3);
});

<?php

use Shelfwood\PhpPms\Mews\Payloads\UpdateCustomerPayload;

it('creates update customer payload', function () {
    $payload = new UpdateCustomerPayload(
        customerId: 'customer-123',
        isActive: false,
        notes: 'test',
        email: 'test@example.com',
        firstName: 'Test',
        lastName: 'User',
        phone: '+31600000000'
    );

    $array = $payload->toArray();
    expect($array['CustomerId'])->toBe('customer-123')
        ->and($array['IsActive'])->toBeFalse();
});

it('throws on missing customer id', function () {
    new UpdateCustomerPayload(customerId: '');
})->throws(\InvalidArgumentException::class, 'CustomerId is required');

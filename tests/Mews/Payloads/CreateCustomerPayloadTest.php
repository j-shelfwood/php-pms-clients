<?php

use Shelfwood\PhpPms\Mews\Payloads\CreateCustomerPayload;

it('creates payload with required lastName only', function () {
    $payload = new CreateCustomerPayload(lastName: 'Smith');

    expect($payload->lastName)->toBe('Smith');
});

it('creates payload with all fields', function () {
    $payload = new CreateCustomerPayload(
        lastName: 'Doe',
        title: 'Mr',
        secondLastName: 'Junior',
        email: 'john.doe@example.com',
        firstName: 'John',
        phone: '+1234567890',
        nationalityCode: 'US',
        preferredLanguageCode: 'en-US',
        birthDate: '1985-03-15',
        address: [
            'Line1' => '123 Main St',
            'City' => 'New York',
            'PostalCode' => '10001',
            'CountryCode' => 'US'
        ]
    );

    expect($payload->title)->toBe('Mr')
        ->and($payload->secondLastName)->toBe('Junior')
        ->and($payload->firstName)->toBe('John')
        ->and($payload->email)->toBe('john.doe@example.com')
        ->and($payload->phone)->toBe('+1234567890');
});

it('converts to array filtering null values', function () {
    $payload = new CreateCustomerPayload(
        lastName: 'Smith',
        email: 'jane@example.com',
        firstName: 'Jane'
    );

    $array = $payload->toArray();

    expect($array)->toHaveKeys(['LastName', 'Email', 'FirstName'])
        ->and($array)->not->toHaveKey('Phone')
        ->and($array)->not->toHaveKey('NationalityCode');
});

it('throws exception when lastName is empty', function () {
    new CreateCustomerPayload(lastName: '');
})->throws(\InvalidArgumentException::class, 'LastName is required');

it('throws exception for invalid email format', function () {
    new CreateCustomerPayload(
        lastName: 'Smith',
        email: 'invalid-email'
    );
})->throws(\InvalidArgumentException::class, 'Invalid email format');

it('allows null email', function () {
    $payload = new CreateCustomerPayload(
        lastName: 'Smith',
        firstName: 'John'
    );

    expect($payload->email)->toBeNull();
});

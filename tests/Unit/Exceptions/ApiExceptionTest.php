<?php

namespace Tests\Unit\Exceptions;

use Shelfwood\PhpPms\Exceptions\ApiException;
use Shelfwood\PhpPms\Exceptions\ErrorDetails;

test('ApiException can handle string error codes', function () {
    $errorDetails = new ErrorDetails('INVALID_REQUEST', 'Invalid request format');
    $exception = new ApiException('Test error', 'INVALID_REQUEST', null, $errorDetails);

    expect($exception->getMessage())->toBe('Test error');
    expect($exception->getCode())->toBe(0); // PHP Exception code is int, should be 0 for non-numeric
    expect($exception->originalCode)->toBe('INVALID_REQUEST'); // Our custom property preserves original
    expect($exception->errorDetails->code)->toBe('INVALID_REQUEST');
    expect($exception->errorDetails->message)->toBe('Invalid request format');
});

test('ApiException can handle numeric string error codes', function () {
    $errorDetails = new ErrorDetails('123', 'Numeric error code');
    $exception = new ApiException('Test error', '123', null, $errorDetails);

    expect($exception->getMessage())->toBe('Test error');
    expect($exception->getCode())->toBe(123); // Should convert to int
    expect($exception->originalCode)->toBe('123'); // Original preserved as string
    expect($exception->errorDetails->code)->toBe('123');
});

test('ApiException can handle integer error codes', function () {
    $errorDetails = new ErrorDetails(456, 'Integer error code');
    $exception = new ApiException('Test error', 456, null, $errorDetails);

    expect($exception->getMessage())->toBe('Test error');
    expect($exception->getCode())->toBe(456);
    expect($exception->originalCode)->toBe(456); // Original preserved as int
    expect($exception->errorDetails->code)->toBe('456'); // ErrorDetails converts to string
});
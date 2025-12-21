<?php

use Shelfwood\PhpPms\Mews\Exceptions\MewsApiException;
use Shelfwood\PhpPms\Exceptions\ApiException;

it('extends ApiException', function () {
    $exception = new MewsApiException('Test error', 400);

    expect($exception)->toBeInstanceOf(ApiException::class)
        ->and($exception->getMessage())->toBe('Test error')
        ->and($exception->getCode())->toBe(400);
});

it('accepts previous exception', function () {
    $previous = new \RuntimeException('Previous error');
    $exception = new MewsApiException('Test error', 500, $previous);

    expect($exception->getPrevious())->toBe($previous);
});

<?php

namespace Shelfwood\PhpPms\Exceptions;

/**
 * Error Details Container
 *
 * Holds structured error information from API responses.
 */
class ErrorDetails
{
    /**
     * @param string|null $code Error code from API
     * @param string $message Error message
     * @param array<string, mixed> $rawResponseFragment Raw API response fragment for debugging
     */
    public function __construct(
        public readonly ?string $code,
        public readonly string $message,
        public readonly array $rawResponseFragment = []
    ) {}
}

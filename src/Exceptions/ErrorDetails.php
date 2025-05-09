<?php

namespace Shelfwood\PhpPms\Exceptions;

class ErrorDetails
{
    public function __construct(
        public readonly ?string $code,
        public readonly string $message,
        public readonly array $rawResponseFragment = []
    ) {}
}

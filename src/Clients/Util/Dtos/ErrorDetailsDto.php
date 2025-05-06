<?php

namespace Shelfwood\PhpPms\Clients\Util\Dtos;

class ErrorDetailsDto
{
    public function __construct(
        public readonly ?string $code,
        public readonly string $message,
        public readonly array $rawResponseFragment = []
    ) {}
}

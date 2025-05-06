<?php

namespace Shelfwood\PhpPms\Clients\Exceptions;

class HttpClientException extends \Exception
{
    public function __construct(string $message, int $code = 0)
    {
        parent::__construct($message, $code);
    }
}

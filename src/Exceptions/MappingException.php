<?php

namespace Domain\Connections\Exceptions;

use Exception;

class MappingException extends Exception
{
    public function __construct(string $message, int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
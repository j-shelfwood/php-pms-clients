<?php

namespace Shelfwood\PhpPms\Exceptions;

use Exception;

class ParseException extends Exception
{
    public function __construct(string $message, int $code = 0)
    {
        parent::__construct($message, $code);
    }
}

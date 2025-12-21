<?php

namespace Shelfwood\PhpPms\Mews\Exceptions;

use Shelfwood\PhpPms\Exceptions\ApiException;
use Shelfwood\PhpPms\Exceptions\ErrorDetails;

class MewsApiException extends ApiException
{
    public function __construct(
        string $message,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $details = new ErrorDetails(
            code: (string)$code,
            message: $message,
            rawResponseFragment: []
        );
        
        parent::__construct($message, (string)$code, $previous, $details);
    }
}

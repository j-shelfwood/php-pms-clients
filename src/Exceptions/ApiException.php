<?php

namespace Shelfwood\PhpPms\Exceptions;

use Shelfwood\PhpPms\Exceptions\PmsClientException;
use Shelfwood\PhpPms\Exceptions\ErrorDetails;

class ApiException extends PmsClientException {
    public ErrorDetails $errorDetails;
    public function __construct($message, $code = 0, $previous = null, ?ErrorDetails $details = null) {
        parent::__construct($message, $code, $previous);
        $this->errorDetails = $details;
    }
}
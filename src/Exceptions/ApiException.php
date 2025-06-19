<?php

namespace Shelfwood\PhpPms\Exceptions;

use Shelfwood\PhpPms\Exceptions\PmsClientException;
use Shelfwood\PhpPms\Exceptions\ErrorDetails;

class ApiException extends PmsClientException {
    public ErrorDetails $errorDetails;
    public readonly string|int $originalCode;

    public function __construct($message, string|int $code = 0, $previous = null, ?ErrorDetails $details = null) {
        // Store the original code (string or int)
        $this->originalCode = $code;

        // Convert to int for parent Exception class compatibility
        $intCode = is_numeric($code) ? (int)$code : 0;

        parent::__construct($message, $intCode, $previous);
        $this->errorDetails = $details;
    }

    /**
     * Get the original error code as returned by the API (may be string or int)
     */
    public function getOriginalCode(): string|int
    {
        return $this->originalCode;
    }
}
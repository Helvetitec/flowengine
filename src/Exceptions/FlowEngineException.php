<?php

namespace Helvetitec\FlowEngine\Exceptions;

use Exception;
use Throwable;

class FlowEngineException extends Exception
{
    public array $context;

    public function __construct(
        string $message,
        int $code,
        array|null $context = null,
        Throwable|null $previous = null
    )
    {
        $this->context = $context;
        parent::__construct($message, $code, $previous);
    }
}
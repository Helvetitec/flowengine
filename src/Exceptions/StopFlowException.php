<?php

namespace Helvetitec\FlowEngine\Exceptions;

class StopFlowException extends \Exception
{
    public bool $persist;

    public function __construct(bool $persist = true)
    {
        $this->persist = $persist;
    }
}
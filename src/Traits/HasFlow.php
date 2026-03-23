<?php

namespace Helvetitec\FlowEngine\Traits;

use Helvetitec\FlowEngine\FlowEngine;

trait HasFlow
{
    public function flowEngine(): FlowEngine
    {
        return app($this->flow_engine_class);
    }

    public function runFlow(mixed $input = null): void
    {
        $this->flowEngine()->handle($this, $input);
    }
}
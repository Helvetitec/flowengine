<?php

namespace Helvetitec\FlowEngine\Traits;

use Helvetitec\FlowEngine\FlowEngine;

trait HasFlow
{
    public function resolveFlow(): FlowEngine
    {
        $flow = app($this->flow_class);
        if (!$flow instanceof FlowEngine) {
            throw new \RuntimeException("Invalid flow class: {$this->flow_class}");
        }
        return $flow;
    }

    public function runFlow(mixed $input = null): void
    {
        $this->resolveFlow()->run($this, $input);
    }
}
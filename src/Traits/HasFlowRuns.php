<?php

namespace Helvetitec\FlowEngine\Traits;

use Helvetitec\FlowEngine\Models\FlowRun;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasFlowRuns
{
    public function flowRuns(): MorphMany
    {
        return $this->morphMany(FlowRun::class, 'subject');
    }

    public function runFlow(string $flowClass, mixed $input = null, bool $force = false)
    {
        $flowRun = $this->flowRuns()->firstOrCreate(
            [
                'flow_class' => $flowClass
            ],
            [
                'state_key' => 'start', 
                'active' => true
            ]
        );

        $flowRun->runFlow($input, $force);
    }
}
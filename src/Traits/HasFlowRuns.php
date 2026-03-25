<?php

namespace Helvetitec\FlowEngine\Traits;

use Helvetitec\FlowEngine\Models\FlowRun;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LogicException;

trait HasFlowRuns
{
    public function flowRuns(): MorphMany
    {
        return $this->morphMany(FlowRun::class, 'subject');
    }

    /**
     * Starts the flow with the FlowClass.
     * **Important:** This will not run the flow, but only create or set it!
     *
     * @param string $flowClass
     * @return FlowRun
     */
    public function startFlow(string $flowClass): FlowRun
    {
        return $this->flowRuns()->firstOrCreate(
            [
                'flow_class' => $flowClass
            ],
            [
                'state_key' => 'start',
                'active' => true
            ]
        );
    }

    /**
     * Starts the flow and runs it.
     *
     * @param string $flowClass
     * @param mixed $input
     * @param boolean $force
     * @return void
     */
    public function runFlow(string $flowClass, mixed $input = null, bool $force = false)
    {
        $this->startFlow($flowClass)->runFlow($input, $force);
    }

    /**
     * Broadcasts the context for all flowruns of this model. This will add the fields and overwrites already existing ones.
     *
     * @param array $data
     * @return void
     */
    public function broadcastContext(array $data): void
    {
        $this->flowRuns()->each(function ($flowRun) use ($data){
            if(!$flowRun instanceof FlowRun){
                throw new LogicException("Object is not of type FlowRun but ".$flowRun::class."!");
            }
            $flowRun->mergeContext($data)->persist();
        });
    }
}
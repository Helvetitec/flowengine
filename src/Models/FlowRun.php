<?php

namespace Helvetitec\FlowEngine\Models;

use Carbon\Carbon;
use Helvetitec\FlowEngine\Contracts\FlowSubject;
use Helvetitec\FlowEngine\FlowEngine;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $state_key
 * @property array|null $context
 * @property string $flow_class
 * @property \Carbon\Carbon|null $cooldown_until
 * @property bool $active
 */
class FlowRun extends Model implements FlowSubject
{
    protected $table = 'flow_runs';

    protected $fillable = [
        'subject_type',
        'subject_id',
        'flow_class',
        'state_key',
        'context',
        'cooldown_until',
        'active'
    ];

    protected $casts = [
        'context' => 'array',
        'cooldown_until' => 'datetime',
        'active' => 'boolean'
    ];

    public function subject()
    {
        return $this->morphTo('subject');
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
    
    public function getStateKey(): string
    {
        return $this->state_key;
    }

    public function setStateKey(string $state): void
    {
        $this->state_key = $state;
    }

    public function getContext(): array
    {
        return $this->context ?? [];
    }

    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    public function getCooldown(): ?Carbon
    {
        return $this->cooldown_until;
    }

    public function setCooldown(Carbon $until): void
    {
        $this->cooldown_until = $until;
    }

    public function persist(): void
    {
        $this->save();
    }

    /**
     * Resolves the current FlowEngine running based on flow_class.
     *
     * @return FlowEngine
     */
    public function resolveFlow(): FlowEngine
    {
        $flow = app($this->flow_class);
        if (!$flow instanceof FlowEngine) {
            throw new \RuntimeException("Invalid flow class: {$this->flow_class}");
        }
        return $flow;
    }

    /**
     * Run the flow with a certain input. If force is set to true it will overwrite any cooldown or active state.
     *
     * @param mixed $input
     * @param boolean $force
     * @return void
     */
    public function runFlow(mixed $input = null, bool $force = false): void
    {
        if($force){
            $this->active = true;
            $this->cooldown_until = null;
        }
        $this->resolveFlow()->run($this, $input);
    }

    /**
     * Adds the data to the context.
     *
     * @param array $data
     * @return static
     */
    public function mergeContext(array $data): static
    {
        $this->setContext([
            ...$this->getContext(),
            ...$data,
        ]);

        return $this;
    }

    /**
     * Removes all FlowRuns with a certain flowClass, flowType and flowId older than $clearOlderThan.
     * 
     * @param string $flowClass
     * @param string|null $flowType
     * @param string|null $flowId
     * @param Carbon|null $clearOlderThan
     * @return int
     */
    public static function clear(string $flowClass, ?string $flowType = null, ?string $flowId = null, ?Carbon $clearOlderThan = null): int
    {
        return FlowRun::where('flow_class', '=', $flowClass)
            ->when($clearOlderThan, function($query) use($clearOlderThan){
                $query->where('updated_at', '<', $clearOlderThan);
            })
            ->when(!empty($flowType), function($query) use($flowType){
                $query->where('flow_type', '=', $flowType);
            })
            ->when(!empty($flowId), function($query) use($flowId){
                $query->where('flow_id', '=', $flowId);
            })
            ->delete();
    }
}
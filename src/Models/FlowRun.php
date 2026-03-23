<?php

namespace Helvetitec\FlowEngine\Models;

use Carbon\Carbon;
use Helvetitec\FlowEngine\Contracts\FlowSubject;
use Helvetitec\FlowEngine\FlowEngine;
use Illuminate\Database\Eloquent\Model;

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

    public function resolveFlow(): FlowEngine
    {
        $flow = app($this->flow_class);
        if (!$flow instanceof FlowEngine) {
            throw new \RuntimeException("Invalid flow class: {$this->flow_class}");
        }
        return $flow;
    }

    public function runFlow(mixed $input = null, bool $force = false): void
    {
        if($force){
            $this->active = true;
            $this->cooldown_until = null;
        }
        $this->resolveFlow()->run($this, $input);
    }
}
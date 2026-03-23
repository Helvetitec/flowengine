<?php

namespace Helvetitec\FlowEngine\Examples;

use Carbon\Carbon;
use Helvetitec\FlowEngine\Contracts\FlowSubject;
use Illuminate\Database\Eloquent\Model;

class ChatFlowExampleModel extends Model implements FlowSubject
{
    protected $casts = [
        'context' => 'array',
        'cooldown_until' => 'datetime',
    ];

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
}
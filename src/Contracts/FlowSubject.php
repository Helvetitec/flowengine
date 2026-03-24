<?php

namespace Helvetitec\FlowEngine\Contracts;

use Carbon\Carbon;

interface FlowSubject
{
    public function getActive(): bool;
    public function setActive(bool $active): void;
    
    public function getStateKey(): string;
    public function setStateKey(string $state): void;

    public function getContext(): array;
    public function setContext(array $context): void;

    public function getCooldown(): ?Carbon;
    public function setCooldown(Carbon $until): void;
    
    public function persist(): void;
}
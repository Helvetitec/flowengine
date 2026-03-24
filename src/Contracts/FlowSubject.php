<?php

namespace Helvetitec\FlowEngine\Contracts;

use Carbon\Carbon;

interface FlowSubject
{
    /**
     * Returns the current active state of the flow. If this returns false, the flow won't run except it is forced to.
     *
     * @return boolean
     */
    public function getActive(): bool;
    /**
     * Set the current active state of the flow.
     *
     * @param boolean $active
     * @return void
     */
    public function setActive(bool $active): void;
    
    /**
     * Returns the key of the current state
     *
     * @return string
     */
    public function getStateKey(): string;
    /**
     * Sets the key for the new state.
     *
     * @param string $state
     * @return void
     */
    public function setStateKey(string $state): void;

    /**
     * Returns the context as array.
     *
     * @return array
     */
    public function getContext(): array;
    /**
     * Sets the current context
     *
     * @param array $context
     * @return void
     */
    public function setContext(array $context): void;

    /**
     * Returns a Carbon instance of the cooldown.
     *
     * @return Carbon|null
     */
    public function getCooldown(): ?Carbon;
    /**
     * Sets the cooldown from a Carbon instance.
     *
     * @param Carbon $until
     * @return void
     */
    public function setCooldown(Carbon $until): void;
    
    /**
     * Persists the data of the Subject.
     *
     * @return void
     */
    public function persist(): void;
}
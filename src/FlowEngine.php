<?php 

namespace Helvetitec\FlowEngine;

use Carbon\Carbon;
use Helvetitec\FlowEngine\Contracts\FlowSubject;
use Helvetitec\FlowEngine\Exceptions\StopFlowException;

abstract class FlowEngine
{
    protected FlowSubject $subject;

    /**
     * Call run() instead. Do not use this on its own.
     *
     * @param mixed $input
     * @return void
     */
    abstract protected function doRun(mixed $input): void;
    
    /**
     * Runs the FlowEngine if possible. Won't run if cooldown is set.
     *
     * @param FlowSubject $subject
     * @param mixed $input
     * @return void
     */
    final public function run(FlowSubject $subject, mixed $input): void
    {
        if($subject->getCooldown() && now()->lt($subject->getCooldown())){
            return;
        }
        
        $this->subject = $subject;

        try{
            $this->doRun($input);
            $subject->persist();
        }catch(StopFlowException $e){
            if($e->persist){
                $subject->persist();
            }
        }
    }

    /**
     * Sets the cooldown for the next run.
     *
     * @param Carbon $until
     * @return static
     */
    final protected function cooldown(Carbon $until): static
    {
        $this->subject->setCooldown($until);
        return $this;
    }

    /**
     * Sets the state for the next run.
     *
     * @param string $nextState
     * @return static
     */
    final protected function transition(string $nextState): static
    {
        $this->subject->setStateKey($nextState);
        return $this;
    }

    /**
     * Sets the context for the next run.
     *
     * @param string $key
     * @param mixed $value
     * @return static
     */
    final protected function set(string $key, mixed $value): static
    {
        $context = $this->subject->getContext();
        $context[$key] = $value;
        $this->subject->setContext($context);
        return $this;
    }

    /**
     * Gets the context with a specific key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    final protected function get(string $key, mixed $default = null): mixed
    {
        return $this->subject->getContext()[$key] ?? $default;
    }

    /**
     * Stops the flow.
     *
     * @param boolean $persist
     * @return never
     */
    final protected function stop(bool $persist = true): never
    {
        throw new StopFlowException($persist);
    }
}
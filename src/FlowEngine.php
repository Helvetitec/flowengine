<?php 

namespace Helvetitec\FlowEngine;

use Carbon\Carbon;
use Helvetitec\FlowEngine\Contracts\FlowSubject;
use Helvetitec\FlowEngine\Exceptions\FlowEngineException;
use Helvetitec\FlowEngine\Exceptions\StopFlowException;
use LogicException;

abstract class FlowEngine
{
    private FlowSubject $subject;

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
        
        if(!$subject->getActive()){
            return;
        }

        if($subject->getCooldown() && $subject->getCooldown()?->isFuture()){
            return;
        }
        
        $this->subject = $subject;

        try{
            $this->doRun($input);
        }catch(StopFlowException $e){
            if($e->persist){
                $subject->persist();
            }
        }catch(\Throwable $e){
            $exceptionContext = [
                'flow_engine_class' => $this::class,
                'flow_engine_context' => $subject?->getContext(),
                'flow_engine_state' => $subject?->getStateKey(),
                'input' => $input
            ];
            throw new FlowEngineException($e->getMessage(), $e->getCode(), $exceptionContext, $e);
        }
    }

    /**
     * Returns the current subject of the FlowEngine
     *
     * @return FlowSubject
     */
    final protected function subject(): FlowSubject
    {
        if (!isset($this->subject)) {
            throw new LogicException('The subject was not set. Did you call run?');
        }

        return $this->subject;
    }

    /**
     * Sets the cooldown for the next run.
     *
     * @param ?Carbon $until
     * @return static
     */
    final protected function cooldown(?Carbon $until): static
    {
        $this->subject()->setCooldown($until);
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
        $this->subject()->setStateKey($nextState);
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
        $context = $this->subject()->getContext();
        $context[$key] = $value;
        $this->subject()->setContext($context);
        return $this;
    }

    /**
     * Gets an item from the context or returns the default value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    final protected function get(string $key, mixed $default = null): mixed
    {
        return $this->subject()->getContext()[$key] ?? $default;
    }

    /**
     * Pulls an item from the context an removes it or returns the default value.
     *
     * @param string $key
     * @param mixed $default
     * @param bool $persist
     * @return mixed
     */
    final protected function pull(string $key, mixed $default = null, bool $persist = false): mixed
    {
        $context = $this->subject()->getContext();
        $value = $context[$key] ?? $default;
        $this->delete($key);
        if($persist){
            $this->subject()->persist();
        }
        return $value;
    }

    /**
     * Deletes an item from the context.
     *
     * @param string $key
     * @return static
     */
    final protected function delete(string $key): static
    {
        $context = $this->subject()->getContext();
        if(array_key_exists($key, $context)){
            unset($context[$key]);
            $this->subject()->setContext(count($context) < 1 ? null : $context);
        }
        return $this;
    }

    /**
     * Removes everything from the context.
     *
     * @return static
     */
    protected function clear(): static
    {
        $this->subject->setContext(null);
        return $this;
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

    /**
     * Sets the transition and stops. Persist will always set to true here as it wouldn't make sense otherwise. 
     *
     * @param string $nextState
     * @return never
     */
    final protected function transitionAndStop(string $nextState): never
    {
        $this->transition($nextState);
        $this->stop(true);
    }

    /**
     * Deactivates the flow and stops it.
     *
     * @return never
     */
    final protected function deactivate(): never
    {
        $this->subject()->setCooldown(null);
        $this->subject()->setActive(false);
        $this->stop(true);
    }

    /**
     * Pauses the flow for a certain amount of time
     *
     * @param Carbon $cooldownUntil
     * @return never
     */
    protected function pause(Carbon $cooldownUntil): never
    {
        $this->cooldown($cooldownUntil);
        $this->stop(true);
    }

    /**
     * Resets the flow to the initial state and optionally applies a cooldown and deletes the context.
     *
     * @param Carbon|null $cooldownUntil
     * @param boolean $deleteContext
     * @return never
     */
    final protected function reset(?Carbon $cooldownUntil = null, bool $deleteContext = false): never
    {
        if($cooldownUntil){
            $this->cooldown($cooldownUntil);
        }

        if($deleteContext){
            $this->clear();
        }

        $this->transition('start');
        $this->stop(true);
    }
}
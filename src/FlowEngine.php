<?php 

namespace Helvetitec\FlowEngine;

use Carbon\Carbon;
use Helvetitec\FlowEngine\Contracts\FlowSubject;
use Helvetitec\FlowEngine\Exceptions\StopFlowException;

abstract class FlowEngine
{
    protected FlowSubject $subject;

    abstract protected function doRun(mixed $input): void;
    
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

    final protected function cooldown(Carbon $until): static
    {
        $this->subject->setCooldown($until);
        return $this;
    }

    final protected function transition(string $nextState): static
    {
        $this->subject->setStateKey($nextState);
        return $this;
    }

    final protected function set(string $key, mixed $value): static
    {
        $context = $this->subject->getContext();
        $context[$key] = $value;
        $this->subject->setContext($context);
        return $this;
    }

    final protected function get(string $key, mixed $default = null): mixed
    {
        return $this->subject->getContext()[$key] ?? $default;
    }

    final protected function stop(bool $persist = true): never
    {
        throw new StopFlowException($persist);
    }
}
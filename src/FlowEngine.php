<?php 

namespace Helvetitec\FlowEngine;

use Helvetitec\FlowEngine\Contracts\FlowSubject;

abstract class FlowEngine
{
    abstract public function handle(FlowSubject $subject, mixed $input): void;

    protected function transition(FlowSubject $subject, string $nextState): void
    {
        $subject->setStateKey($nextState);
        $subject->persist();
    }
}
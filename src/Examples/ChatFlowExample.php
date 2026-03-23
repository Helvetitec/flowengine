<?php

namespace Helvetitec\FlowEngine\Examples;

use Helvetitec\FlowEngine\FlowEngine;
use Illuminate\Support\Facades\Log;

class ChatFlowExample extends FlowEngine
{
    protected function doRun(mixed $input): void
    {
        $state = $this->subject->getStateKey();

        match($state){
            'start' => $this->start(),
            'waiting' => $this->handleAnswer($input),
            default => $this->start(),
        };
    }

    protected function start(): void
    {
        Log::debug("Sending some message...");

        $this->transition('waiting')
             ->stop();
    }

    protected function handleAnswer($input): void
    {
        Log::debug("Handle this input: {$input}");
        $this->transition('done')
             ->cooldown(now()->addMinutes(5))
             ->stop();
    }
}
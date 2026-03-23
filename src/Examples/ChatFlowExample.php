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
        Log::debug("Sending message: \"Option 1 or 2?\"...");

        $this->transition('waiting')
            ->set('options', [1,2])
            ->stop();
    }

    protected function handleAnswer($input): void
    {
        $options = $this->get('options');
        if(!in_array($input, $options)){
            Log::debug("Invalid input, as it is not inside options context");
            $this->stop();
            return;
        }
        Log::debug("Handle the input '{$input}'");
        $this->transition('done')
             ->cooldown(now()->addMinutes(5))
             ->stop();
    }
}
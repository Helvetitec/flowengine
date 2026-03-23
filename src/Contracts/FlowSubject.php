<?php

namespace Helvetitec\FlowEngine\Contracts;

interface FlowSubject
{
    public function getStateKey(): string;
    public function setStateKey(string $state): void;

    public function getContext(): array;
    public function setContext(array $context): void;

    public function persist(): void;
}
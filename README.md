# FlowEngine

A simple, flexible state machine engine for Laravel to handle dynamic flows like chats, onboarding, workflows, and more.


## 🚀 Concept

FlowEngine allows you to define **state-driven flows** where each subject (e.g. a chat, user, or process) moves through states based on input.

Typical flow:

1. Receive input
2. Process current state
3. Transition to next state
4. Persist state + context
5. Stop execution
6. Resume later (via new input or cooldown)

## Setup

### Normal usage
No further setup needed

### With FlowRuns
1) Publish the migrations:
```ps
php artisan vendor:publish --tag="helvetitec.flowengine.migrations"
```
2) Run migrations:
```ps
php artisan migrate
```

## 🧱 Core Components

### FlowEngine

The base class that handles execution:

```php
abstract class FlowEngine
{
    abstract protected function doRun(mixed $input): void;

    final public function run(FlowSubject $subject, mixed $input): void;

    final protected function cooldown(Carbon $until): static;

    final protected function transition(string $nextState): static;

    final protected function set(string $key, mixed $value): static;

    final protected function get(string $key, mixed $default = null): mixed

    final protected function stop(bool $persist = true): never;
}
```

### FlowSubject

Any model that participates in a flow must implement:

```php
interface FlowSubject
{
    public function getActive(): bool;
    public function setActive(bool $active): void;

    public function getStateKey(): string;
    public function setStateKey(string $state): void;

    public function getContext(): array;
    public function setContext(array $context): void;

    public function getCooldown(): Carbon;
    public function setCooldown(Carbon $until): void;

    public function persist(): void;
}
```


## ⚡ Example Implementation

### 1.1. Subject (e.g. Chat)

If you only want to use one flow at a time.

```php
class Chat extends Model implements FlowSubject
{
    use HasFlow;

    protected $casts = [
        'context' => 'array',
        'cooldown_until' => 'datetime',
        'active' => 'boolean'
    ];

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

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
```

### 1.2. FlowRun (Recomended)

If you want you can use the FlowRuns which would allow multiple FlowEngines running at the same time.

**Important:** If you use this, please run php artisan vendor:publish --tag="helvetitec.flowengine.migrations"

```php
//Add to your Subject (e.g Chat)
use HasFlowRuns;
```

### 2. Flow

```php
class ChatFlow extends FlowEngine
{
    protected function doRun(mixed $input): void
    {
        $state = $this->subject->getStateKey();

        match ($state) {
            'start' => $this->start(),
            'waiting' => $this->handleAnswer($input),
            default => $this->start(),
        };
    }

    protected function start(): void
    {
        ChatService::send($this->subject, "Choose 1 or 2");

        $this->transition('waiting')
             ->set('options', [1,2]) //Sets the context for the flow
             ->stop();
    }

    protected function handleAnswer($input): void
    {
        $options = $this->get('options');
        if(!in_array($input, $options)){
            ChatService::send($this->subject, "Invalid input");
            $this->stop();
            return;
        }
        ChatService::send($this->subject, "You chose: {$input}");

        $this->transition('done')
             ->cooldown(now()->addMinutes(5))
             ->stop();
    }
}
```


### 3. Triggering the Flow

```php
app(ChatFlow::class)->run($chat, $message);
```

or

```php 
//With use HasFlow;
$chat->runFlow($message);
```

or 

```php
//With use HasFlowRuns;
$chat->runFlow(ChatFlow::class, $message, $force);
```

You typically call this from:

* Controllers
* Jobs
* Event listeners
* Webhooks


## 🔁 Flow Lifecycle

```
Input → run() → doRun()
        ↓
   state logic
        ↓
 transition()
 set()
 cooldown()
        ↓
     stop()
        ↓
   persist()
```


## 🧩 Helper Methods

### Transition state

```php
$this->transition('next_state');
```


### Store data in context

```php
$this->set('key', 'value');

$value = $this->get('key');
```


### Cooldown

```php
$this->cooldown(now()->addMinutes(10));
```


### Stop execution

```php
$this->stop(); //persists
```

or

```php
$this->stop(persist: false); //does not persist
```


## ⚠️ Important Rules

### 1. Always use `run()`

```php
// ✅ Correct
app(MyFlow::class)->run($subject, $input);

// ❌ Wrong
$flow->doRun($input);
```


### 2. Always stop after sending output

```php
$this->transition('next')
     ->stop();
```


### 3. Do not persist manually

Persistence is handled automatically by the engine.


### 4. Use state keys as strings

```php
'start'
'waiting_for_input'
'completed'
```

## 5. Hints

### Enable/Disable flows

You can fully disable flows by setting the setActive/getActive methods to a custom field or use setActive in FlowRuns.
```php
protected $fillable = [
    'flow_active'
];

protected $casts = [
    'flow_active' => 'boolean'
];

public function setActive(bool $active): void
{
    $this->flow_active = $active;
}

public function getActive(): bool
{
    return $this->flow_active;
}
```

## 6. 📄 AI Usage

AI was used to create this readme file and for smaller parts of the code to make it cleaner.

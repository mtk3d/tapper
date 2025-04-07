<?php

namespace Tapper\Console;

class State
{
    private array $state = [];

    private array $listeners = [];

    public function set(string $key, mixed $value): void
    {
        $this->delete($key);
        $this->state[$key] = $value;
        $this->callListeners($key);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (isset($this->state[$key])) {
            return $this->state[$key];
        }

        return $default;
    }

    public function delete(string $key): void
    {
        if (isset($this->state[$key])) {
            unset($this->state[$key]);
            $this->callListeners($key);
        }
    }

    public function append(string $key, mixed $value): void
    {
        if (! isset($this->state[$key])) {
            $this->state[$key] = $value;
            $this->callListeners($key);

            return;
        }

        if (is_string($this->state[$key])) {
            $this->state[$key] .= $value;
            $this->callListeners($key);

            return;
        }

        if (is_array($this->state[$key])) {
            $this->state[$key][] = $value;
            $this->callListeners($key);

            return;
        }
    }

    public function onChange(string $key, callable $listener): void
    {
        $this->listeners[$key][] = $listener;
    }

    private function callListeners(string $key): void
    {
        if (! array_key_exists($key, $this->listeners)) {
            return;
        }

        foreach ($this->listeners[$key] as $listener) {
            $listener($this->state[$key]);
        }
    }
}

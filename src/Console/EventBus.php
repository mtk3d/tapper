<?php

namespace Tapper\Console;

use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\Event\FunctionKeyEvent;
use PhpTui\Term\Event\MouseEvent;
use PhpTui\Term\KeyCode;
use PhpTui\Term\MouseEventKind;

class EventBus
{
    private $register = [];

    public function listen(
        MouseEventKind|KeyCode|string|int $event,
        callable $func,
    ): void {
        if ($event instanceof KeyCode) {
            $event = $event->name;
        }

        if ($event instanceof MouseEventKind) {
            $event = $event->name;
        }

        if (! isset($this->register[$event])) {
            $this->register[$event] = [];
        }

        $this->register[$event][] = $func;
    }

    public function emit(
        CharKeyEvent|CodedKeyEvent|FunctionKeyEvent|MouseEvent|string|int $event,
        array $data = []
    ): void {
        $key = $event;
        if ($event instanceof CharKeyEvent) {
            $data = [
                'modifiers' => $event->modifiers,
                ...$data,
            ];
        }

        if ($event instanceof CharKeyEvent) {
            $key = $event->char;
        }

        if ($event instanceof CodedKeyEvent) {
            $key = $event->code->name;
        }

        if ($event instanceof FunctionKeyEvent) {
            $key = "F{$event->number}";
        }

        if ($event instanceof MouseEvent) {
            $key = $event->kind->name;
            $data = [
                'event' => $event,
            ];
        }

        if (! isset($this->register[$key])) {
            return;
        }

        foreach ($this->register[$key] as $func) {
            $func($data);
        }
    }
}

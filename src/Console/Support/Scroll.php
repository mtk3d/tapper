<?php

declare(strict_types=1);

namespace Tapper\Console\Support;

use Tapper\Console\State\AppState;

class Scroll
{
    public function __construct(private readonly AppState $appState) {}

    public function cursorDown(int $count, int $visible): void
    {
        $this->appState->deffer();

        if ($this->appState->cursor < $count - 1) {
            $this->appState->cursor++;
        }

        if ($this->appState->cursor > $this->appState->offset + $visible - 1) {
            $this->appState->offset++;
        }

        $this->appState->commit();
    }

    public function cursorUp(int $count, int $visible): void
    {
        $this->appState->deffer();

        if ($this->appState->cursor > 0) {
            $this->appState->cursor--;
        }

        if ($this->appState->cursor < $this->appState->offset) {
            $this->appState->offset--;
        }

        $this->appState->commit();
    }

    public function scrollDown(int $count, int $visible): void
    {
        $this->appState->deffer();

        if ($this->appState->offset + $visible < $count) {
            $this->appState->offset++;
        }

        if ($this->appState->offset > $this->appState->cursor) {
            $this->appState->cursor++;
        }

        $this->appState->commit();
    }

    public function scrollUp(int $count, int $visible): void
    {
        $this->appState->deffer();

        if ($this->appState->offset > 0) {
            $this->appState->offset--;
        }

        if ($this->appState->offset + $visible <= $this->appState->cursor) {
            $this->appState->cursor--;
        }

        $this->appState->commit();
    }

    public function scrollToBottom(int $count, int $visible): void
    {
        $this->appState->deffer();

        $this->appState->cursor = $count - 1;
        $this->appState->offset = $count - $visible;

        $this->appState->commit();
    }

    public function jump(int $position, int $count, int $visible): void
    {
        if ($position < 0) {
            $position = 0;
        }

        if ($position > $count - 1) {
            $position = $count - 1;
        }

        $this->appState->deffer();

        if ($this->appState->cursor === $position) {
            return;
        }

        $this->appState->cursor = $position;

        if ($this->appState->cursor < $this->appState->offset) {
            $this->appState->offset = $this->appState->cursor;
        }

        if ($this->appState->cursor > $this->appState->offset + $visible - 1) {
            $this->appState->offset = max(0, $this->appState->cursor - $visible + 1);
        }

        $this->appState->commit();
    }
}

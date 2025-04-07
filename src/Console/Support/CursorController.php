<?php

declare(strict_types=1);

namespace Tapper\Console\Support;

class CursorController
{
    public function __construct(
        public int $count,
        public int $maxItems,
        public int $cursor = 0,
        public int $offset = 0,
    ) {}

    public function moveUp(): void
    {
        if ($this->cursor > 0) {
            $this->cursor--;
        } elseif ($this->offset > 0) {
            $this->offset--;
        }
    }

    public function moveDown(): void
    {
        $maxOffset = max(0, $this->count - $this->maxItems);

        if ($this->cursor < $this->count - 1 && $this->cursor < $this->maxItems - 1) {
            $this->cursor++;
        } elseif ($this->offset < $maxOffset) {
            $this->offset++;
        }
    }

    public function follow(): void
    {
        $this->offset = max(0, $this->count - $this->maxItems);
        $this->cursor = min($this->count, $this->maxItems) - 1;
    }
}

<?php

declare(strict_types=1);

namespace Tapper\Console\State;

class LogItem
{
    public function __construct(
        public int $id,
        public float $timestamp,
        public string $message,
        public string $caller,
    ) {}
}

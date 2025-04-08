<?php

declare(strict_types=1);

namespace Tapper;

function tp($value, string $label = 'debug'): void
{
    (new \Tapper\Runtime\Tapper)->tap($value, $label);
}

function tpp(string $message = 'Paused'): void
{
    (new \Tapper\Runtime\Tapper)->tapPause($message);
}

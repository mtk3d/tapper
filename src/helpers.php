<?php

declare(strict_types=1);

if (! function_exists('tp')) {
    function tp($value, string $label = 'debug'): void
    {
        (new \Tapper\Runtime\Tapper)->tap($value, $label);
    }
}

if (! function_exists('tpp')) {
    function tpp(string $message = 'Paused'): void
    {
        (new \Tapper\Runtime\Tapper)->tapPause($message);
    }
}

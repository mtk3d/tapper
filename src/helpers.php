<?php

declare(strict_types=1);

use Tapper\Runtime\Tapper;

if (! function_exists('tp')) {
    function tp($value): Tapper
    {
        return (new Tapper)->tap($value);
    }
}

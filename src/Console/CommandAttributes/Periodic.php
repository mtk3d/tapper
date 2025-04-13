<?php

declare(strict_types=1);

namespace Tapper\Console\CommandAttributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Periodic
{
    public function __construct(
        public readonly float $interval,
    ) {}
}

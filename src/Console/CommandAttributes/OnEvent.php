<?php

namespace Tapper\Console\CommandAttributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class OnEvent
{
    public bool $global = true;

    public function __construct(
        public readonly string $key,
    ) {}
}

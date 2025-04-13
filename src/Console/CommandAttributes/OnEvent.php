<?php

declare(strict_types=1);

namespace Tapper\Console\CommandAttributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class OnEvent
{
    public function __construct(
        public readonly string $key,
        public readonly bool $global = false,
    ) {}
}

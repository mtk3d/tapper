<?php

declare(strict_types=1);

namespace Tapper\Console\CommandAttributes;

use Attribute;
use PhpTui\Term\MouseEventKind;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Mouse
{
    public function __construct(
        public readonly MouseEventKind $key,
        public readonly bool $global = false,
    ) {}
}

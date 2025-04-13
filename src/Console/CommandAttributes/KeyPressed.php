<?php

declare(strict_types=1);

namespace Tapper\Console\CommandAttributes;

use Attribute;
use PhpTui\Term\KeyCode;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class KeyPressed
{
    public function __construct(
        public readonly KeyCode|string $key,
        public readonly ?int $keyModifiers = null,
        public readonly bool $global = false,
    ) {}
}

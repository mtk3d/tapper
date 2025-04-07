<?php

declare(strict_types=1);

namespace Tapper\Console\Support;

use DI\Container;

class VirtualListManager
{
    public function __construct(
        private Container $container,
        private string $componentClass,
        private array &$listItems,
    ) {}

    public function ensureVisible(int $visible): void
    {
        $existing = count($this->listItems);

        if ($visible > $existing) {
            for ($i = $existing; $i < $visible; $i++) {
                $this->listItems[] = $this->container->make($this->componentClass);
            }
        }

        if ($visible < $existing) {
            $this->listItems = array_slice($this->listItems, 0, $visible);
        }
    }

    public function fill(array $logs, int $offset, int $cursor): void
    {
        foreach ($this->listItems as $i => $component) {
            $logIndex = $offset + $i;
            $log = $logs[$logIndex] ?? null;

            if ($log !== null) {
                $component->setData($log);

                if ($logIndex === $cursor) {
                    $component->select();
                } else {
                    $component->deselect();
                }
            }
        }
    }
}

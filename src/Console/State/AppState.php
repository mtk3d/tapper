<?php

declare(strict_types=1);

namespace Tapper\Console\State;

use RuntimeException;

/**
 * @property string $version
 * @property int $port
 * @property bool $live
 * @property bool $typingMode
 * @property int $cursor
 * @property int $offset
 * @property ?LogItem $previewLog
 * @property array $logs
 */
class AppState
{
    private array $observers = [];

    /**
     * @param  LogItem[]  $logs
     */
    public function __construct(
        private string $version = '',
        private int $port = 2137,
        private bool $live = true,
        private bool $typingMode = false,
        private int $cursor = 0,
        private int $offset = 0,
        private ?LogItem $previewLog = null,
        private array $logs = [],
    ) {}

    /**
     * @return LogItem[]
     */
    public function logs(): array
    {
        return $this->logs;
    }

    public function appendLog(LogItem $logItem): void
    {
        $this->logs[] = $logItem;
        $this->callObservers('logs');
    }

    public function observe(string $name, callable $callable): void
    {
        $params = get_class_vars($this::class);
        unset($params['observers']);
        $params = array_keys($params);

        if (! in_array($name, $params)) {
            throw new RuntimeException(sprintf('Cannot observe state. %s is not defined.', $name));
        }

        if (! array_key_exists($name, $this->observers)) {
            $this->observers[$name] = [];
        }

        $this->observers[$name][] = $callable;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
        $this->callObservers($name);
    }

    public function __get($name)
    {
        return $this->$name;
    }

    private function callObservers(string $name): void
    {
        if (! array_key_exists($name, $this->observers)) {
            return;
        }

        foreach ($this->observers[$name] as $observer) {
            $observer($this->$name);
        }
    }
}

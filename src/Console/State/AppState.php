<?php

declare(strict_types=1);

namespace Tapper\Console\State;

use RuntimeException;

/**
 * @property string $version
 * @property int $port
 * @property bool $live
 * @property bool $showDot
 * @property bool $typingMode
 * @property int $cursor
 * @property int $offset
 * @property int $unread
 * @property ?LogItem $previewLog
 * @property array $logs
 */
class AppState
{
    private array $observers = [];

    private $change = null;

    private $batching = false;

    private $changed = [];

    /**
     * @param  LogItem[]  $logs
     */
    public function __construct(
        private string $version = '',
        private int $port = 2137,
        private bool $live = true,
        private bool $showDot = true,
        private bool $typingMode = false,
        private int $cursor = 0,
        private int $offset = 0,
        private int $unread = 0,
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

    public function setOnChange(callable $change): void
    {
        $this->change = $change;
    }

    public function appendLog(LogItem $logItem): void
    {
        $this->logs[] = $logItem;
        if (! $this->batching) {
            $this->notifyChange();
            $this->callObservers('logs');
        }
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

    public function deffer(): void
    {
        $this->changed = [];
        $this->batching = true;
    }

    public function commit(): void
    {
        $this->notifyChange();

        foreach ($this->changed as $field) {
            $this->callObservers($field);
        }

        $this->changed = [];
        $this->batching = false;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
        if (! $this->batching) {
            $this->notifyChange();
            $this->callObservers($name);
        }
    }

    public function __get($name)
    {
        return $this->$name;
    }

    private function notifyChange(): void
    {
        if ($this->change) {
            ($this->change)();
        }
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

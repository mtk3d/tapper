<?php

declare(strict_types=1);

namespace Tapper\Runtime;

use Tapper\Rpc\JsonRpcClient;
use Tapper\Rpc\JsonRpcRequest;

class Tapper
{
    private static ?JsonRpcClient $client = null;

    private string $caller;

    private mixed $message;

    private float $microtime;

    private string $type = 'log';

    private array $trace = [];

    private ?string $rootDir = null;

    private array $callerToGet;

    public function __construct()
    {
        if (self::$client === null) {
            self::$client = new JsonRpcClient;
        }

        $this->collectDebugInfo();
    }

    public function tap(mixed $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function wait(): self
    {
        $this->type = 'wait';

        return $this;
    }

    public function __destruct()
    {
        $this->send();
    }

    private function collectDebugInfo(): void
    {
        $this->rootDir = $this->findProjectRoot();
        $backtrace = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 2);
        $this->trace = array_map(function ($frame) {
            return [
                'file' => $frame['file'] ?? null,
                'line' => $frame['line'] ?? null,
            ];
        }, $backtrace);

        $caller = $this->trace[0] ?? null;
        $this->caller = $caller
            ? sprintf('%s:%s', basename($caller['file']), $caller['line'])
            : 'faled to get caller';

        $this->callerToGet = $caller;

        $this->microtime = microtime(true);
    }

    private function findProjectRoot(): ?string
    {
        $composerClassLoader = \Composer\Autoload\ClassLoader::class;

        if (! class_exists($composerClassLoader)) {
            return null;
        }

        $reflector = new \ReflectionClass($composerClassLoader);
        $pathToClassLoader = $reflector->getFileName();

        return dirname(dirname(dirname($pathToClassLoader)));
    }

    private function getCodeExcerpt(string $file, int $line, int $context = 3): array
    {
        if (! is_file($file) || ! is_readable($file)) {
            return [];
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES);
        $start = max(0, $line - $context - 1);
        $end = min(count($lines), $line + $context);

        $excerpt = [];

        for ($i = $start; $i < $end; $i++) {
            $excerpt[] = [
                'number' => $i + 1,
                'line' => $lines[$i],
                'active' => ($i + 1 === $line),
            ];
        }

        return $excerpt;
    }

    private function send(): void
    {
        $payload = [
            'message' => $this->message,
            'caller' => $this->caller,
            'microtime' => $this->microtime,
            'trace' => $this->trace,
            'rootDir' => $this->rootDir,
            'code' => $this->getCodeExcerpt($this->callerToGet['file'], $this->callerToGet['line']),
        ];

        $request = new JsonRpcRequest(
            $this->type,
            $payload,
        );

        $response = self::$client->call($request);
        $result = $response['result'] ?? null;

        if (! in_array($result, ['ok', 'continue'])) {
            throw new \Exception('[Tapper] server not responding.');
        }
    }
}

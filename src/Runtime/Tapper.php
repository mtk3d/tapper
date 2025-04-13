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

    private function send(): void
    {
        $payload = [
            'message' => $this->message,
            'caller' => $this->caller,
            'microtime' => $this->microtime,
            'trace' => $this->trace,
            'rootDir' => $this->rootDir,
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

<?php

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
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = $trace[2] ?? null;
        $this->caller = $caller
            ? sprintf('%s:%s', basename($caller['file']), $caller['line'])
            : 'faled to get caller';

        $this->microtime = microtime(true);
    }

    private function send(): void
    {
        $payload = [
            'message' => $this->message,
            'caller' => $this->caller,
            'microtime' => $this->microtime,
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

<?php

namespace Tapper\Runtime;

use Tapper\Rpc\JsonRpcClient;
use Tapper\Rpc\JsonRpcRequest;

class Tapper
{
    private static ?JsonRpcClient $client = null;

    public function __construct()
    {
        if (self::$client === null) {
            self::$client = new JsonRpcClient;
        }
    }

    public function tap($value, string $label = 'debug'): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $trace[1] ?? null;
        $trace = basename($caller['file']).':'.$caller['line'];

        $request = new JsonRpcRequest('log', ['message' => $value, 'trace' => $trace, 'microtime' => microtime(true)]);
        $response = self::$client->call($request);

        if (! isset($response['result']) || $response['result'] !== 'ok') {
            error_log('[Tapper] tap failed or no response from server.');
            exit;
        }

    }

    public function tapPause(string $message = 'paused...'): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $trace[1] ?? null;
        $trace = basename($caller['file']).':'.$caller['line'];

        $request = new JsonRpcRequest('pause', ['message' => $message, 'trace' => $trace, 'microtime' => microtime(true)]);
        $response = self::$client->call($request);

        if (! isset($response['result']) || $response['result'] !== 'continue') {
            error_log('[Tapper] pause failed or no response from server.');
            exit;
        }
    }
}

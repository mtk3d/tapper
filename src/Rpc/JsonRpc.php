<?php

declare(strict_types=1);

namespace Tapper\Rpc;

interface JsonRpc
{
    public function payload(): array;
    // public static function encodeRequest(string $method, array $params = [], ?string $id = null): string
    // {
    //     return json_encode([
    //         'jsonrpc' => '2.0',
    //         'method' => $method,
    //         'params' => $params,
    //         'id' => $id ?? uniqid('rpc_', true),
    //     ])."\n";
    // }
    //
    // public static function encodeResult($result, string $id): string
    // {
    //     return json_encode([
    //         'jsonrpc' => '2.0',
    //         'result' => $result,
    //         'id' => $id,
    //     ])."\n";
    // }
    //
    // public static function encodeError(string $message, int $code = -32603, $id = null): string
    // {
    //     return json_encode([
    //         'jsonrpc' => '2.0',
    //         'error' => [
    //             'code' => $code,
    //             'message' => $message,
    //         ],
    //         'id' => $id,
    //     ])."\n";
    // }
    //
    // public static function parse(string $json): ?array
    // {
    //     $data = json_decode($json, true);
    //
    //     if (! is_array($data) || ($data['jsonrpc'] ?? null) !== '2.0') {
    //         return null;
    //     }
    //
    //     return $data;
    // }
}

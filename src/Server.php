<?php

namespace Tapper;

use Clue\React\NDJson\Decoder;
use Clue\React\NDJson\Encoder;
use PhpTui\Term\KeyCode;
use React\Socket\SocketServer;
use Tapper\Console\EventBus;
use Tapper\Console\State;

class Server
{
    private static $id = 0;

    public function run(State $state, EventBus $eventBus): void
    {
        $state->set('logs', []);
        $server = new SocketServer('127.0.0.1:2137');

        $server->on('connection', function (\React\Socket\ConnectionInterface $conn) use ($state, $eventBus) {
            $decoder = new Decoder($conn, true);
            $encoder = new Encoder($conn, true);

            $decoder->on('data', function ($message) use ($encoder, $state, $eventBus) {

                if (($message['jsonrpc'] ?? '') !== '2.0') {
                    $encoder->write([
                        'jsonrpc' => '2.0',
                        'error' => [
                            'code' => -32600,
                            'message' => 'Invalid Request',
                        ],
                        'id' => $message['id'] ?? null,
                    ]);

                    return;
                }

                $method = $message['method'] ?? '';
                $params = $message['params'] ?? [];
                $id = $message['id'] ?? null;

                switch ($method) {
                    case 'log':
                        $state->append('logs', [
                            'microtime' => $params['microtime'],
                            'message' => json_encode($params['message'], JSON_UNESCAPED_UNICODE),
                            'trace' => $params['trace'],
                        ]);

                        $encoder->write([
                            'jsonrpc' => '2.0',
                            'result' => 'ok',
                            'id' => $id,
                        ]);
                        break;

                    case 'pause':
                        $state->append('logs', [
                            'microtime' => $params['microtime'],
                            'message' => "⏸ {$params['message']} — press ENTER to continue",
                            'trace' => $params['trace'],
                        ]);

                        $eventBus->listen(KeyCode::Enter, fn () => ($encoder->write([
                            'jsonrpc' => '2.0',
                            'result' => 'continue',
                            'id' => $id,
                        ])
                        ));

                        break;

                    default:
                        $encoder->write([
                            'jsonrpc' => '2.0',
                            'error' => [
                                'code' => -32601,
                                'message' => "Method '{$method}' not found",
                            ],
                            'id' => $id,
                        ]);
                }
            });
        });
    }
}

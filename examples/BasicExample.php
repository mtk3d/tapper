<?php

require __DIR__.'/../vendor/autoload.php';

tp('Start debugging');

tp('Wait for debugger')->wait();

for ($i = 0; $i < 3; $i++) {
    tp("Log: $i");
}

tp('Show some arrays');

tp(['id' => 1, 'task' => 'resize_image', 'sync' => true]);

tp('Wait for debugger')->wait();

tp(['id' => 2, 'task' => 'send_email', 'sync' => true]);

for ($i = 0; $i < 3; $i++) {
    tp("Log: $i");
}

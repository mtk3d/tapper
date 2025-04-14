<?php

require __DIR__.'/../vendor/autoload.php';

tp('👋 Hello, this is Tapper');
usleep(500000);
tp('A tiny debugger for PHP');
usleep(500000);
tp('You can send debug messages, just like console.log in JS');

usleep(1000000);
tp('It can also send structured data:');
usleep(500000);
tp(['fruits' => ['apple', 'banana', 'pineapple']]);

usleep(1000000);
tp('Or even very long structured data');
usleep(500000);
tp(['fruits' => ['apple', 'banana', 'pineapple', 'orange', 'strawberry', 'blueberry', 'pear'], 'accounts' => [['name' => 'Alice', 'email' => 'alice@ec.net', 'subscribe' => false, 'age' => 26], ['name' => 'John', 'email' => 'john@ec.net', 'subscribe' => true, 'age' => 21], ['name' => 'Jane', 'email' => 'jane@ec.net', 'subscribe' => true, 'age' => 31]]]);

usleep(1000000);
tp('You can pause code execution...');
foreach (range(1, 3) as $i) {
    tp("Paused in loop at iteration $i")->wait();
    tp('That will run after wait');
}

<?php

require __DIR__.'/../vendor/autoload.php';

tp('👋 Hello, this is Tapper');
tp('A tiny debugger for PHP');
tp('You can send debug messages, just like console.log in JS');

tp('It can also send structured data:');
tp(['fruits' => ['apple', 'banana', 'pineapple']]);

tp('You can pause code execution...');
foreach (range(1, 3) as $i) {
    tp("Paused in loop at iteration $i")->wait();
    tp('That will run after wait');
}
tp('Now your turn');

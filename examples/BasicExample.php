<?php

require __DIR__.'/../vendor/autoload.php';

$jobs = [
    ['id' => 1, 'task' => 'resize_image', 'sync' => true],
    ['id' => 2, 'task' => 'send_email', 'sync' => true],
    ['id' => 3, 'task' => 'generate_pdf', 'sync' => true],
];

tp('Start debugging');

tp('Show some jsons');

tp($jobs[1]);

tp('Wait for debugger')->wait();

tp($jobs[2]);

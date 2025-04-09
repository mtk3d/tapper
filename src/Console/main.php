<?php

use PhpTui\Term\Terminal;
use PhpTui\Tui\Display\Display;
use PhpTui\Tui\DisplayBuilder;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use Tapper\Console\Application;

$builder = new \DI\ContainerBuilder;
$builder->useAutowiring(true);
$builder->useAttributes(true);
$builder->addDefinitions([
    LoopInterface::class => fn () => Loop::get(),
    Terminal::class => fn () => Terminal::new(),
    Display::class => fn () => DisplayBuilder::default()->build(),
]);

$container = $builder->build();

return $container->get(Application::class);

<?php

namespace Tapper\Console\Windows;

use Tapper\Console\Component;

abstract class Window extends Component
{
    public function unmount(): void
    {
        foreach ($this->timers as $timer) {
            $timer->stop();
        }
    }
}

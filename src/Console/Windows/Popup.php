<?php

namespace Tapper\Console\Windows;

use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Widget\Widget;

class Popup extends Window
{
    public function init(): void {}

    public function render(Area $area): Widget
    {
        return BlockWidget::default();
    }
}

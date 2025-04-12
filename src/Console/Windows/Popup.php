<?php

namespace Tapper\Console\Windows;

use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Widget\Widget;
use Tapper\Console\Component;

class Popup extends Component
{
    protected function view(Area $area): Widget
    {
        return BlockWidget::default();
    }
}

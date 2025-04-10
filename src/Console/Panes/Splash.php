<?php

declare(strict_types=1);

namespace Tapper\Console\Panes;

use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Text\Line;
use PhpTui\Tui\Widget\HorizontalAlignment;
use PhpTui\Tui\Widget\Widget;
use Tapper\Console\Component;

class Splash extends Component
{
    public function render(Area $area): Widget
    {
        $name = 'T A P P E R';

        return BlockWidget::default()
            ->widget(
                ParagraphWidget::fromLines(
                    Line::fromString(''),
                    Line::fromString(''),
                    Line::fromString($name)->alignment(HorizontalAlignment::Center),
                    Line::fromString($this->appState->version)->alignment(HorizontalAlignment::Center)->darkGray(),
                )
            );
    }
}

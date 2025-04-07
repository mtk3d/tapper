<?php

namespace Tapper\Console\Panes;

use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Style\Style;
use PhpTui\Tui\Text\Span;
use PhpTui\Tui\Widget\Borders;
use PhpTui\Tui\Widget\BorderType;
use PhpTui\Tui\Widget\Widget;
use Tapper\Console\CommandAttributes\Periodic;

class Header extends Pane
{
    private $show = true;

    public function init(): void {}

    public function mount(): void {}

    #[Periodic(0.8)]
    public function blink(): void
    {
        $this->show = ! $this->show;
    }

    public function render(Area $area): Widget
    {
        $this->area = $area;

        $follow = $this->state->get('follow_log', true);

        return
            BlockWidget::default()
                ->borders(Borders::BOTTOM)
                ->borderType(BorderType::Plain)
                ->borderStyle($this->isActive ? Style::default()->white() : Style::default()->gray())
                ->widget(
                    ParagraphWidget::fromSpans(
                        Span::fromString('Tapper v0.1 | port: 2137 |'),
                        Span::fromString(' '),
                        Span::fromString($follow ? ($this->show ? '●' : ' ') : '⏸'),
                        Span::fromString(' '),
                        Span::fromString($follow ? 'LIVE' : 'PAUSED'),
                    ),
                );
    }
}

<?php

namespace Tapper\Console\Panes;

use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Text\Span;
use PhpTui\Tui\Widget\Borders;
use PhpTui\Tui\Widget\BorderType;
use PhpTui\Tui\Widget\Widget;
use Tapper\Console\CommandAttributes\Periodic;
use Tapper\Console\Component;

class Header extends Component
{
    private $show = true;

    public function init(): void {}

    public function mount(): void {}

    #[Periodic(0.8)]
    public function blink(): void
    {
        $this->show = ! $this->show;
    }

    public function view(Area $area): Widget
    {
        $live = $this->appState->live;

        return
            BlockWidget::default()
                ->borders(Borders::BOTTOM)
                ->borderType(BorderType::Plain)
                ->widget(
                    ParagraphWidget::fromSpans(
                        Span::fromString(sprintf('Tapper %s | port: %s |', $this->appState->version, $this->appState->port)),
                        Span::fromString(' '),
                        $live ? Span::fromString($this->show ? '●' : ' ')->red() : Span::fromString('⏸')->blue(),
                        Span::fromString(' '),
                        Span::fromString($live ? 'LIVE' : 'PAUSED'),
                    ),
                );
    }
}

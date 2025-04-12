<?php

namespace Tapper\Console\Panes;

use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Text\Span;
use PhpTui\Tui\Widget\Borders;
use PhpTui\Tui\Widget\BorderType;
use PhpTui\Tui\Widget\Widget;
use Tapper\Console\Component;

class Header extends Component
{
    protected function view(Area $area): Widget
    {
        $live = $this->appState->live;

        $unread = $this->appState->unread > 0;

        return
            BlockWidget::default()
                ->borders(Borders::BOTTOM)
                ->borderType(BorderType::Plain)
                ->widget(
                    ParagraphWidget::fromSpans(
                        Span::fromString(' '),
                        $live ? Span::fromString('●')->red() : Span::fromString('⏸')->blue(),
                        Span::fromString(' '),
                        Span::fromString($live ? 'LIVE' : 'PAUSED'),
                        $unread ? Span::fromString(sprintf(' (↓%s)', $this->appState->unread))->yellow() : Span::fromString(''),
                        Span::fromString(' | '),
                        Span::fromString(sprintf('port: %s', $this->appState->port)),
                    ),
                );
    }
}

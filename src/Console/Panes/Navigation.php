<?php

namespace Tapper\Console\Panes;

use PhpTui\Term\KeyCode;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Style\Style;
use PhpTui\Tui\Text\Span;
use PhpTui\Tui\Widget\Borders;
use PhpTui\Tui\Widget\BorderType;
use PhpTui\Tui\Widget\Widget;

class Navigation extends Pane
{
    public function init(): void {}

    public function mount(): void {}

    public function render(Area $area): Widget
    {
        $this->area = $area;

        $buttonStyle = Style::default();
        $delimiter = Span::fromString(' | ');

        return
            BlockWidget::default()
                ->borders(Borders::TOP)
                ->borderType(BorderType::Plain)
                ->borderStyle($this->isActive ? Style::default()->white() : Style::default()->gray())
                ->widget(
                    // ParagraphWidget::fromString('[SPACE] details   [ESC] back   [ENTER] continue   [↑/↓] navigation   [q] quit'),
                    ParagraphWidget::fromSpans(
                        Span::fromString('Details: '),
                        Span::styled('space', $buttonStyle),
                        $delimiter,

                        Span::fromString('Back: '),
                        Span::styled('esc', $buttonStyle),
                        $delimiter,

                        Span::fromString('Continue: '),
                        Span::styled('enter', $buttonStyle),
                        $delimiter,

                        Span::fromString('Navigation: '),
                        Span::styled('↑/↓', $buttonStyle),
                        $delimiter,

                        Span::fromString('Quit: '),
                        Span::styled('q', $buttonStyle),
                    )->style(Style::default()->blue()),
                );
    }
}

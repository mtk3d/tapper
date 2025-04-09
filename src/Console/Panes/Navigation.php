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

class Navigation extends Pane
{
    public function init(): void {}

    public function mount(): void {}

    public function render(Area $area): Widget
    {
        $this->area = $area;

        $buttonStyle = Style::default();
        $delimiter = Span::fromString(' | ');

        $instruction = [];

        if (!$this->appState->live && ! $this->appState->previewLog) {
            $instruction[] = Span::fromString('Live: ');
            $instruction[] = Span::styled('esc', $buttonStyle);
            $instruction[] = $delimiter;
        }

        if ($this->appState->previewLog) {
            $instruction[] = Span::fromString('Back: ');
            $instruction[] = Span::styled('esc', $buttonStyle);
            $instruction[] = $delimiter;
        } else {
            $instruction[] = Span::fromString('Details: ');
            $instruction[] = Span::styled('space', $buttonStyle);
            $instruction[] = $delimiter;

        }

        $instruction[] = Span::fromString('Continue: ');
        $instruction[] = Span::styled('enter', $buttonStyle);
        $instruction[] = $delimiter;

        $instruction[] = Span::fromString('Navigation: ');
        $instruction[] = Span::styled('↑/↓', $buttonStyle);
        $instruction[] = $delimiter;

        $instruction[] = Span::fromString('Quit: ');
        $instruction[] = Span::styled('q', $buttonStyle);

        return
            BlockWidget::default()
                ->borders(Borders::TOP)
                ->borderType(BorderType::Plain)
                ->borderStyle($this->isActive ? Style::default()->white() : Style::default()->gray())
                ->widget(
                    ParagraphWidget::fromSpans(...$instruction)->style(Style::default()->blue()),
                );
    }
}

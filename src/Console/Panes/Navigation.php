<?php

namespace Tapper\Console\Panes;

use PhpTui\Tui\Color\RgbColor;
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

        $buttonStyle = Style::default()->fg(RgbColor::fromHex('7aa2f7'));
        $descStyle = Style::default()->fg(RgbColor::fromHex('7aa2f7'));
        $delimiter = Span::styled(' | ', $descStyle);

        $instruction = [];

        if (! $this->appState->live && ! $this->appState->previewLog) {
            $instruction[] = Span::styled('Live: ', $descStyle, $descStyle);
            $instruction[] = Span::styled('esc', $buttonStyle);
            $instruction[] = $delimiter;
        }

        if ($this->appState->previewLog) {
            $instruction[] = Span::styled('Back: ', $descStyle);
            $instruction[] = Span::styled('esc', $buttonStyle);
            $instruction[] = $delimiter;
        } else {
            $instruction[] = Span::styled('Details: ', $descStyle);
            $instruction[] = Span::styled('space', $buttonStyle);
            $instruction[] = $delimiter;

        }

        $instruction[] = Span::styled('Continue: ', $descStyle);
        $instruction[] = Span::styled('enter', $buttonStyle);
        $instruction[] = $delimiter;

        $instruction[] = Span::styled('Navigation: ', $descStyle);
        $instruction[] = Span::styled('↑/↓', $buttonStyle);
        $instruction[] = $delimiter;

        $instruction[] = Span::styled('Quit: ', $descStyle);
        $instruction[] = Span::styled('q', $buttonStyle);

        return
            BlockWidget::default()
                ->borders(Borders::TOP)
                ->borderType(BorderType::Plain)
                ->widget(
                    ParagraphWidget::fromSpans(...$instruction),
                );
    }
}

<?php

declare(strict_types=1);

namespace Tapper\Console\Components;

use DateTime;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Style\Style;
use PhpTui\Tui\Text\Line;
use PhpTui\Tui\Text\Span;
use PhpTui\Tui\Widget\Direction;
use PhpTui\Tui\Widget\Widget;
use Tapper\Console\Component;
use Tapper\Console\MessageFormatter;

class LogItem extends Component
{
    private bool $selected = false;

    private array $log = [];

    public function select(): void
    {
        $this->selected = true;
    }

    public function deselect(): void
    {
        $this->selected = false;
    }

    public function setData(array $data): void
    {
        $this->log = $data;
    }

    public function render(Area $area): Widget
    {
        [$microtime, $message, $trace] = array_values($this->log);

        $dt = DateTime::createFromFormat('U.u', sprintf('%.6f', $microtime));
        $date = $dt->format('Y-m-d');
        $time = $dt->format('H:i:s.u');

        $marker = $this->selected ? '█ ' : '  ';

        $darkGray = Style::default()->darkGray();

        return BlockWidget::default()
            ->widget(
                GridWidget::default()
                    ->direction(Direction::Horizontal)
                    ->constraints(
                        Constraint::length(20),
                        Constraint::length($area->width - 20),
                    )->widgets(
                        ParagraphWidget::fromLines(
                            Line::fromSpans(Span::styled($marker, $darkGray), Span::styled("$time", Style::default()->blue())),
                            Line::fromSpans(Span::styled($marker, $darkGray), Span::styled("$date", $darkGray)),
                        ),
                        ParagraphWidget::fromLines(
                            json_validate($message) ? Line::fromSpans(...MessageFormatter::colorizeInlineJson($message)) : Line::fromSpan(Span::fromString("$message")),
                            Line::fromSpan(Span::styled("↪ $trace", $darkGray)),
                        ),
                    ),
            );
    }
}

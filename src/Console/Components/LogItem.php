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
use Tapper\Console\State\LogItem as LogItemState;

class LogItem extends Component
{
    private ?LogItemState $log = null;

    public function setData(LogItemState $log): void
    {
        $this->log = $log;
    }

    public function render(Area $area): Widget
    {
        if (! $this->log) {
            return BlockWidget::default();
        }

        $dt = DateTime::createFromFormat('U.u', sprintf('%.6f', $this->log->timestamp));
        $date = $dt->format('Y-m-d');
        $time = $dt->format('H:i:s.u');

        $marker = $this->appState->cursor === $this->log->id ? '█ ' : '  ';

        $darkGray = Style::default()->darkGray();

        $message = $this->log->message;

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
                            Line::fromSpan(Span::styled(sprintf('↪ %s', $this->log->caller), $darkGray)),
                        ),
                    ),
            );
    }
}

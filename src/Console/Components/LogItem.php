<?php

declare(strict_types=1);

namespace Tapper\Console\Components;

use DateTime;
use PhpTui\Tui\Color\RgbColor;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Style\Style;
use PhpTui\Tui\Text\Line;
use PhpTui\Tui\Text\Span;
use PhpTui\Tui\Text\Text;
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

        $mark = $this->appState->cursor === $this->log->id;

        $darkGray = Style::default()->darkGray();
        $markerColor = RgbColor::fromHex('2a2e42');
        $mStyle = Style::default()->bg($markerColor);

        $message = $this->log->message;

        $firstLine = ParagraphWidget::fromText(
            Text::fromLines(
                Line::fromSpans(Span::styled("$time", Style::default()->fg(RgbColor::fromHex('7aa2f7')))),
                Line::fromSpans(Span::styled("$date", $darkGray)),
            )
        );

        $wMess = ParagraphWidget::fromSpans(...MessageFormatter::colorizeInlineJson($message));
        $wFile = ParagraphWidget::fromSpans(Span::styled(sprintf('↪ %s', $this->log->caller), $darkGray));

        if ($mark) {
            $firstLine->style($mStyle);
            $wMess->style($mStyle);
            $wFile->style($mStyle);
        }

        $firstLine = GridWidget::default()
            ->direction(Direction::Vertical)
            ->constraints(Constraint::length(2), Constraint::length(1))
            ->widgets($firstLine);

        $secondLine = GridWidget::default()
            ->direction(Direction::Vertical)
            ->constraints(Constraint::length(1), Constraint::length(1), Constraint::length(1))
            ->widgets(
                $wMess,
                $wFile,
            );

        return BlockWidget::default()
            ->widget(
                GridWidget::default()
                    ->direction(Direction::Horizontal)
                    ->constraints(
                        Constraint::length(17),
                        Constraint::length($area->width - 17),
                    )->widgets(
                        $firstLine,
                        $secondLine,
                    ),
            );
    }
}

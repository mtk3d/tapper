<?php

namespace Tapper\Console\Panes;

use DateTime;
use PhpTui\Term\KeyCode;
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
use Tapper\Console\CommandAttributes\KeyPressed;
use Tapper\Console\MessageFormatter;

class LogList extends Pane
{
    private int $offset = 0;

    private int $maxItems = 0;

    private int $count = 0;

    private int $cursor = 0;

    public function init(): void {}

    public function mount(): void {}

    #[KeyPressed(KeyCode::Esc)]
    public function exitUserNav(): void
    {
        $this->state->set('follow_log', true);
    }

    #[KeyPressed(KeyCode::Up)]
    #[KeyPressed('k')]
    public function up(): void
    {
        if ($this->cursor > 0) {
            $this->state->set('follow_log', false);
            $this->cursor--;
            return;
        }

        if ($this->offset > 0) {
            $this->state->set('follow_log', false);
            $this->offset--;
        }
    }

    #[KeyPressed(KeyCode::Down)]
    #[KeyPressed('j')]
    public function down(): void
    {
        $maxOffset = $this->count - $this->maxItems;
        $maxOffset = $maxOffset < 0 ? 0 : $maxOffset;

        if ($this->cursor < $this->count - 1 && $this->cursor < $this->maxItems - 1) {
            $this->state->set('follow_log', false);
            $this->cursor++;
            return;
        }

        if ($this->offset < $maxOffset) {
            $this->state->set('follow_log', false);
            $this->offset++;
        }

        if ($this->offset === $maxOffset) {
            $this->state->set('follow_log', true);
        }
    }

    #[KeyPressed(' ')]
    public function select(): void
    {
        $this->state->set('details_index', $this->cursor);
        $this->eventBus->emit('log_details', ['index' => $this->cursor]);
    }

    public function render(Area $area): Widget
    {
        $logs = $this->state->get('logs', []);

        $this->maxItems = floor($area->height / 3);
        $this->count = count($logs);

        if ($this->state->get('follow_log', true)) {
            $this->offset = max(0, $this->count - $this->maxItems);
            $this->cursor = min($this->count, $this->maxItems) - 1;
        }

        $logsWindow = array_slice($logs, $this->offset, $this->maxItems);

        $logsWidgets = [];

        foreach ($logsWindow as $index => $log) {
            [$microtime, $message, $trace] = array_values($log);

            $dt = DateTime::createFromFormat('U.u', sprintf('%.6f', $microtime));
            $date = $dt->format('Y-m-d');
            $time = $dt->format('H:i:s.u');

            $selected = $this->cursor === $index;

            $marker = $selected ? '█ ' : '  ';

            $darkGray = Style::default()->darkGray();

            $logsWidgets[] = BlockWidget::default()
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

        return
            BlockWidget::default()
                ->widget(
                    GridWidget::default()
                        ->direction(Direction::Vertical)
                        ->constraints(...array_fill(0, $this->maxItems, Constraint::length(3)))
                        ->widgets(...$logsWidgets)
                );
    }
}

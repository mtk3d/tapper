<?php

namespace Tapper\Console\Panes;

use DateTime;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Text\Line;
use PhpTui\Tui\Widget\Widget;
use Tapper\Console\MessageFormatter;

class Details extends Pane
{
    public function init(): void {}

    public function mount(): void {}

    public function render(Area $area): Widget
    {
        $logs = $this->state->get('logs');
        $index = $this->state->get('details_index');
        $log = $logs[$index] ?? [];
        $message = $log['message'] ?? '';
        $trace = $log['trace'] ?? '';
        $datetime = DateTime::createFromFormat('U.u', sprintf('%.6f', $log['microtime']));
        $formatted = $datetime->format('Y-m-d H:i:s.u');


        return BlockWidget::default()
            ->widget(
                ParagraphWidget::fromLines(
                    Line::fromString(''),
                    Line::fromString("Log #$index | $trace"),
                    Line::fromString($formatted),
                    Line::fromString(''),
                    Line::fromString('──────────────────── Payload ────────────────────'),
                    Line::fromString(''),
                    ...MessageFormatter::colorizeFormattedJson($message)),
            );
    }
}

<?php

namespace Tapper\Console\Panes;

use DateTime;
use PhpTui\Term\KeyCode;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Text\Line;
use PhpTui\Tui\Widget\Widget;
use Tapper\Console\CommandAttributes\KeyPressed;
use Tapper\Console\Component;
use Tapper\Console\MessageFormatter;

class Details extends Component
{
    public function init(): void {}

    public function mount(): void {}

    #[KeyPressed(KeyCode::Backspace, true)]
    #[KeyPressed(KeyCode::Esc, true)]
    public function close(): void
    {
        $this->appState->previewLog = null;
    }

    public function view(Area $area): Widget
    {
        $log = $this->appState->previewLog;
        $datetime = DateTime::createFromFormat('U.u', sprintf('%.6f', $log->timestamp));
        $formatted = $datetime->format('Y-m-d H:i:s.u');

        return BlockWidget::default()
            ->widget(
                ParagraphWidget::fromLines(
                    Line::fromString(''),
                    Line::fromString(sprintf('Log #%s | %s', $log->id, $log->caller)),
                    Line::fromString($formatted),
                    Line::fromString(''),
                    Line::fromString('──────────────────── Payload ────────────────────'),
                    Line::fromString(''),
                    ...MessageFormatter::colorizeFormattedJson($log->message)),
            );
    }
}

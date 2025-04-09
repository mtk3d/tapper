<?php

namespace Tapper\Console\Panes;

use PhpTui\Term\KeyCode;
use PhpTui\Term\MouseEventKind;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Widget\Direction;
use PhpTui\Tui\Widget\Widget;
use Tapper\Console\CommandAttributes\KeyPressed;
use Tapper\Console\CommandAttributes\Mouse;
use Tapper\Console\CommandAttributes\OnEvent;
use Tapper\Console\Component;
use Tapper\Console\Components\LogItem;
use Tapper\Console\Support\VirtualListManager;

class LogList extends Pane
{
    private array $logs = [];

    private array $listItems = [];

    private int $maxItems = 0;

    private int $count = 0;

    private VirtualListManager $list;

    public function mount(): void
    {
        $this->list = new VirtualListManager($this->container, LogItem::class, $this->listItems);

        $this->appState->observe('logs', fn (array $logs): null => $this->updateLogs($logs));
        $this->appState->observe('cursor', function (int $cursor): void {
            $this->appState->live = $cursor >= $this->count - 1;
        });
    }

    public function updateLogs(array $data): void
    {
        $this->logs = $data;
        $this->count = count($data);

        $this->updateVisible();
    }

    #[OnEvent('resize')]
    public function updateVisible(): void
    {
        if ($this->area) {
            $this->maxItems = floor($this->area->height / 3);
        }

        if ($this->appState->live) {
            $this->scrollToBottom();
        }

        $this->list->ensureVisible(min($this->maxItems, $this->count));

        $this->fill();
    }

    public function fill(): void
    {
        $this->list->fill($this->logs, $this->appState->offset);
    }

    #[KeyPressed(KeyCode::Up)]
    #[KeyPressed('k')]
    public function up(): void
    {
        if ($this->appState->cursor > 0) {
            $this->appState->cursor--;
        }

        if ($this->appState->cursor < $this->appState->offset) {
            $this->offsetUp();
        }
    }

    #[Mouse(MouseEventKind::ScrollUp)]
    public function scrollUp(): void
    {
        $this->offsetUp();

        if ($this->appState->cursor >= $this->appState->offset + $this->maxItems) {
            $this->appState->cursor--;
        }
    }

    public function offsetUp(): void
    {
        if ($this->appState->offset > 0) {
            $this->appState->offset--;
            $this->fill();
        }
    }

    #[KeyPressed(KeyCode::Down)]
    #[KeyPressed('j')]
    public function down(): void
    {
        if ($this->appState->cursor < $this->count - 1) {
            $this->appState->cursor++;
        }

        if ($this->appState->cursor > $this->maxItems - 1) {
            $this->offsetDown();
        }
    }

    #[Mouse(MouseEventKind::ScrollDown)]
    public function scrollDown(): void
    {
        $this->offsetDown();

        if ($this->appState->cursor < $this->appState->offset) {
            $this->appState->cursor++;
        }
    }

    public function offsetDown(): void
    {
        if ($this->appState->offset < $this->count - $this->maxItems) {
            $this->appState->offset++;
            $this->fill();
        }
    }

    #[KeyPressed(' ')]
    public function select(): void
    {
        $this->appState->previewLog = $this->appState->logs()[$this->appState->cursor];
    }

    #[KeyPressed(KeyCode::Esc)]
    public function exitUserNav(): void
    {
        $this->scrollToBottom();
        $this->fill();
    }

    private function scrollToBottom(): void
    {
        $newOffset = $this->count - $this->maxItems;

        if ($newOffset > 0) {
            $this->appState->offset = $newOffset;
        }

        $this->appState->cursor = $this->count - 1;
    }

    public function render(Area $area): Widget
    {
        $this->area = $area;

        $this->maxItems = floor($area->height / 3);

        return BlockWidget::default()
            ->widget(
                GridWidget::default()
                    ->direction(Direction::Vertical)
                    ->constraints(...array_fill(0, $this->maxItems, Constraint::length(3)))
                    ->widgets(
                        ...array_map(
                            fn (Component $item): Widget => $item->render($area),
                            $this->listItems
                        )
                    )
            );
    }
}

<?php

namespace Tapper\Console\Panes;

use PhpTui\Term\KeyCode;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Widget\Direction;
use PhpTui\Tui\Widget\Widget;
use Tapper\Console\CommandAttributes\KeyPressed;
use Tapper\Console\CommandAttributes\OnEvent;
use Tapper\Console\Component;
use Tapper\Console\Components\LogItem;
use Tapper\Console\Support\CursorController;
use Tapper\Console\Support\VirtualListManager;

class LogList extends Pane
{
    private array $logs = [];
    private array $listItems = [];

    private int $maxItems = 0;
    private int $count = 0;

    private CursorController $cursor;
    private VirtualListManager $list;

    public function mount(): void
    {
        $this->cursor = new CursorController(0, 0);
        $this->list = new VirtualListManager($this->container, LogItem::class, $this->listItems);

        $this->state->onChange('logs', fn ($data) => $this->updateLogs($data));
    }

    public function updateLogs(array $data): void
    {
        $this->logs = $data;
        $this->count = count($data);
        $this->cursor->count = $this->count;

        $this->updateVisible();
    }

    #[OnEvent('resize')]
    public function updateVisible(): void
    {
        $this->cursor->maxItems = $this->maxItems;

        $this->list->ensureVisible(min($this->maxItems, $this->count));
        $this->fill();
    }

    public function fill(): void
    {
        $this->list->fill($this->logs, $this->cursor->offset, $this->cursor->cursor);
    }

    #[KeyPressed(KeyCode::Up)]
    #[KeyPressed('k')]
    public function up(): void
    {
        $this->state->set('follow_log', false);
        $this->cursor->moveUp();
        $this->fill();
    }

    #[KeyPressed(KeyCode::Down)]
    #[KeyPressed('j')]
    public function down(): void
    {
        $this->state->set('follow_log', false);
        $this->cursor->moveDown();
        $this->fill();
    }

    #[KeyPressed(' ')]
    public function select(): void
    {
        $this->state->set('details_index', $this->cursor->cursor);
        $this->eventBus->emit('log_details', ['index' => $this->cursor->cursor]);
    }

    #[KeyPressed(KeyCode::Esc)]
    public function exitUserNav(): void
    {
        $this->state->set('follow_log', true);
        $this->fill();
    }

    public function render(Area $area): Widget
    {
        $this->maxItems = floor($area->height / 3);

        if ($this->state->get('follow_log', true)) {
            $this->cursor->follow();
        }

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


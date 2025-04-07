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

class LogList extends Pane
{
    private int $offset = 0;

    private int $maxItems = 0;

    private int $count = 0;

    private int $cursor = 0;

    private array $logs = [];

    private array $listItems = [];

    public function init(): void {}

    public function mount(): void
    {

        $this->state->onChange('logs', fn ($data) => $this->updateLogs($data));
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
        $visible = min($this->maxItems, $this->count);
        $existing = count($this->listItems);

        if ($visible > $existing) {
            for ($i = $existing; $i < $visible; $i++) {
                $this->listItems[] = $this->container->make(LogItem::class);
            }
        }

        $this->fill();
    }

    public function fill(): void
    {
        $visibleLogs = array_slice($this->logs, $this->offset, $this->maxItems);

        foreach ($this->listItems as $i => $component) {
            $log = $visibleLogs[$i] ?? null;

            if ($log !== null) {
                $component->setData($log);
            }
        }

        $this->updateSelection();
    }

    private function updateSelection(): void
    {
        foreach ($this->listItems as $i => $component) {
            $logIndex = $this->offset + $i;

            if ($logIndex === $this->cursor) {
                $component->select();
            } else {
                $component->deselect();
            }
        }
    }

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
            $this->fill();

            return;
        }

        if ($this->offset > 0) {
            $this->state->set('follow_log', false);
            $this->offset--;
            $this->fill();
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
            $this->fill();

            return;
        }

        if ($this->offset < $maxOffset) {
            $this->state->set('follow_log', false);
            $this->offset++;
            $this->fill();
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
        $this->maxItems = floor($area->height / 3);

        if ($this->state->get('follow_log', true)) {
            $this->offset = max(0, $this->count - $this->maxItems);
            $this->cursor = min($this->count, $this->maxItems) - 1;
        }

        return
            BlockWidget::default()
                ->widget(
                    GridWidget::default()
                        ->direction(Direction::Vertical)
                        ->constraints(...array_fill(0, $this->maxItems, Constraint::length(3)))
                        ->widgets(
                            ...array_map(
                                fn (Component $item): Widget => $item->render($area),
                                $this->listItems,
                            ),
                        ),
                );
    }
}

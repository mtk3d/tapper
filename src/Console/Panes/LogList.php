<?php

namespace Tapper\Console\Panes;

use PhpTui\Term\KeyCode;
use PhpTui\Term\MouseEventKind;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\CompositeWidget;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Extension\Core\Widget\Scrollbar\ScrollbarOrientation;
use PhpTui\Tui\Extension\Core\Widget\Scrollbar\ScrollbarState;
use PhpTui\Tui\Extension\Core\Widget\Scrollbar\ScrollbarSymbols;
use PhpTui\Tui\Extension\Core\Widget\ScrollbarWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Widget\Direction;
use PhpTui\Tui\Widget\Widget;
use Tapper\Console\CommandAttributes\FirstRender;
use Tapper\Console\CommandAttributes\KeyPressed;
use Tapper\Console\CommandAttributes\Mouse;
use Tapper\Console\CommandAttributes\OnEvent;
use Tapper\Console\Component;
use Tapper\Console\Components\LogItem;
use Tapper\Console\Support\Scroll;

class LogList extends Component
{
    private array $listItems = [];

    private int $visible = 0;

    private int $count = 0;

    private Scroll $scroll;

    public function init(): void
    {
        $this->scroll = new Scroll($this->appState);

        $this->appState->observe('logs', fn (): null => $this->updateLogs());
        $this->appState->observe('cursor', function (int $cursor): void {
            $this->appState->live = $cursor >= $this->count - 1;
        });
        $this->appState->observe('live', fn ($live): bool => $live && $this->appState->unread = 0);
    }

    #[OnEvent('resize')]
    #[FirstRender()]
    public function updateVisible(): void
    {
        if ($this->area) {
            $this->visible = floor($this->area->height / LogItem::HEIGHT);
        }

        $this->ensureVisible();
        $this->backToLive();
    }

    #[KeyPressed(KeyCode::Up)]
    #[KeyPressed('k')]
    public function up(): void
    {
        $this->scroll->cursorUp($this->count, $this->visible);
    }

    #[Mouse(MouseEventKind::ScrollUp)]
    public function scrollUp(): void
    {
        $this->scroll->scrollUp($this->count, $this->visible);
    }

    #[KeyPressed(KeyCode::Down)]
    #[KeyPressed('j')]
    public function down(): void
    {
        $this->scroll->cursorDown($this->count, $this->visible);
    }

    #[Mouse(MouseEventKind::ScrollDown)]
    public function scrollDown(): void
    {
        $this->scroll->scrollDown($this->count, $this->visible);
    }

    #[KeyPressed(' ')]
    public function select(): void
    {
        $this->appState->previewLog = $this->appState->logs()[$this->appState->cursor];
    }

    #[KeyPressed(KeyCode::Esc)]
    public function backToLive(): void
    {
        $this->scroll->scrollToBottom($this->count, $this->visible);
    }

    private function updateLogs(): void
    {
        if (! $this->appState->live) {
            $this->appState->unread++;
        }

        $this->count = count($this->appState->logs());

        if ($this->appState->live) {
            $this->scroll->scrollToBottom($this->count, $this->visible);
        }
    }

    private function ensureVisible(): void
    {
        $visible = $this->visible;
        $existing = count($this->listItems);

        if ($visible > $existing) {
            for ($i = $existing; $i < $visible; $i++) {
                $this->listItems[] = $this->container->make(LogItem::class);
            }
        }

        if ($visible < $existing) {
            $this->listItems = array_slice($this->listItems, 0, $visible);
        }
    }

    private function fill(): void
    {
        foreach ($this->listItems as $i => $component) {
            $logIndex = $this->appState->offset + $i;
            $log = $this->appState->logs()[$logIndex] ?? null;
            $component->setData($log);
        }
    }

    protected function view(Area $area): Widget
    {
        $this->fill();

        return CompositeWidget::fromWidgets(
            GridWidget::default()
                ->direction(Direction::Vertical)
                ->constraints(...array_fill(0, $this->visible, Constraint::length(LogItem::HEIGHT)))
                ->widgets(
                    ...array_map(
                        fn (Component $item): Widget => $item->render($area),
                        $this->listItems
                    ),
                ),
            ScrollbarWidget::default()
                ->state(new ScrollbarState(max(0, $this->count - $this->visible), $this->appState->offset, 1))
                ->orientation(ScrollbarOrientation::VerticalRight)
                ->symbols(new ScrollbarSymbols('│', '█', '', ''))
                ->endSymbol(null)
                ->beginSymbol(null),
        );
    }
}

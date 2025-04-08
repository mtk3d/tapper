<?php

namespace Tapper\Console\Windows;

use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Layout\Layout;
use PhpTui\Tui\Widget\Direction;
use PhpTui\Tui\Widget\Widget;
use Tapper\Console\Panes\Details;
use Tapper\Console\Panes\Header;
use Tapper\Console\Panes\LogList;
use Tapper\Console\Panes\Navigation;
use Tapper\Console\State\LogItem;
use Tapper\Server;

class Main extends Window
{
    protected array $components = [
        Header::class,
        LogList::class,
        Details::class,
        Navigation::class,
    ];

    public function init(): void
    {
        (new Server)->run($this->appState, $this->eventBus);
    }

    public function mount(): void
    {
        $this->componentInstances[LogList::class]->activate();

        $this->appState->observe('previewLog', function (?LogItem $log) {
            if ($log) {
                $this->componentInstances[Details::class]->activate();
                $this->componentInstances[LogList::class]->deactivate();
            } else {
                $this->componentInstances[Details::class]->deactivate();
                $this->componentInstances[LogList::class]->activate();
            }
        });
    }

    public function render(Area $area): Widget
    {
        $verticalConstraints = [
            Constraint::length(2),
            Constraint::length($area->height - 5),
            Constraint::length(3),
        ];
        $verticalLayout = Layout::default()
            ->direction(Direction::Vertical)
            ->constraints($verticalConstraints)
            ->split($area);

        return GridWidget::default()
            ->direction(Direction::Vertical)
            ->constraints(...$verticalConstraints)
            ->widgets(
                $this->renderComponent(Header::class, $verticalLayout->get(0)),
                $this->appState->previewLog !== null ? $this->renderComponent(Details::class, $verticalLayout->get(1)) : $this->renderComponent(LogList::class, $verticalLayout->get(1)),
                $this->renderComponent(Navigation::class, $verticalLayout->get(2)),
            );
    }
}

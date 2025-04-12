<?php

namespace Tapper\Console\Windows;

use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Extension\Core\Widget\GridWidget;
use PhpTui\Tui\Layout\Constraint;
use PhpTui\Tui\Layout\Layout;
use PhpTui\Tui\Widget\Direction;
use PhpTui\Tui\Widget\Widget;
use Tapper\Console\Component;
use Tapper\Console\Panes\Details;
use Tapper\Console\Panes\Header;
use Tapper\Console\Panes\LogList;
use Tapper\Console\Panes\Navigation;
use Tapper\Console\Panes\Splash;
use Tapper\Console\State\LogItem;

class Main extends Component
{
    protected array $components = [
        Header::class,
        Details::class,
        LogList::class,
        Navigation::class,
        Splash::class,
    ];

    private ?Component $mainPane = null;

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

    protected function view(Area $area): Widget
    {
        $verticalConstraints = [
            Constraint::length(2),
            Constraint::length($area->height - 4),
            Constraint::length(2),
        ];
        $verticalLayout = Layout::default()
            ->direction(Direction::Vertical)
            ->constraints($verticalConstraints)
            ->split($area);

        $middle = $verticalLayout->get(1);

        if ($this->appState->previewLog !== null) {
            $this->mainPane = $this->getComponent(Details::class);
        } elseif (count($this->appState->logs) > 0) {
            $this->mainPane = $this->getComponent(LogList::class);
        } else {
            $this->mainPane = $this->getComponent(Splash::class);
        }

        return GridWidget::default()
            ->direction(Direction::Vertical)
            ->constraints(...$verticalConstraints)
            ->widgets(
                $this->renderComponent(Header::class, $verticalLayout->get(0)),
                $this->mainPane->render($middle),
                $this->renderComponent(Navigation::class, $verticalLayout->get(2)),
            );
    }
}

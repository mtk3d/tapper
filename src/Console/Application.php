<?php

namespace Tapper\Console;

use DI\Container;
use PhpTui\Term\Actions;
use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\Event\MouseEvent;
use PhpTui\Term\EventParser;
use PhpTui\Term\KeyCode;
use PhpTui\Term\MouseEventKind;
use PhpTui\Term\Terminal;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Display\Display;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\CompositeWidget;
use React\EventLoop\LoopInterface;
use React\Stream\ReadableResourceStream;
use Tapper\Console\State\AppState;
use Tapper\Console\Windows\Main;
use Tapper\Console\Windows\Popup;
use Tapper\Console\Windows\Window;

class Application
{
    private Window $window;

    private Popup $popup;

    private EventParser $eventParser;

    private bool $typingMode = false;

    private ?Area $previousArea = null;

    public function __construct(
        private LoopInterface $loop,
        private Terminal $terminal,
        private Display $display,
        private EventBus $eventBus,
        private CommandInvoker $commandInvoker,
        private Container $container,
        private AppState $appState,
    ) {
        $this->eventParser = new EventParser;
    }

    public function run(): int
    {
        $this->appState->version = 'v0.1.1';
        $this->terminal->execute(Actions::alternateScreenEnable());
        $this->terminal->execute(Actions::cursorHide());
        $this->terminal->execute(Actions::enableMouseCapture());
        $this->terminal->enableRawMode();
        $this->terminal->execute(Actions::moveCursor(0, 0));
        $this->display->clear();
        $this->init();
        $this->startRendering();
        $this->startInputHandling();
        $this->loop->run();

        $this->loop->addSignal(SIGINT, function () {
            echo 'kill';
        });
        $this->loop->addSignal(SIGTERM, function () {
            echo 'kill';
        });

        return 0;
    }

    public function init(): void
    {
        $this->window = $this->container->make(Main::class);
        $this->popup = $this->container->make(Popup::class);
    }

    public function startRendering(): void
    {
        $this->loop->addPeriodicTimer(1 / 60, function () {
            $area = $this->display->viewportArea();
            if ($area != $this->previousArea) {
                $this->eventBus->emit('resize');
            }

            $this->previousArea = $area;

            $this->display->draw(
                CompositeWidget::fromWidgets(
                    $this->window->render($area),
                    $this->popup->isActive() ? $this->popup->render($area) : BlockWidget::default(),
                ),
            );
        });
    }

    public function startInputHandling(): void
    {
        $this->appState->observe('typingMode', fn (bool $typingMode) => $this->typingMode = $typingMode);

        $stdin = new ReadableResourceStream(STDIN, $this->loop);
        $stdin->on('data', function ($data) {

            $this->eventParser->advance($data, false);

            foreach ($this->eventParser->drain() as $event) {
                if (in_array($event::class, [CharKeyEvent::class, CodedKeyEvent::class, MouseEvent::class])) {
                    if ($event instanceof MouseEvent && $this->typingMode) {
                        if ($event->kind === MouseEventKind::Down) {
                            $this->appState->typingMode = false;
                        } else {
                            continue;
                        }
                    }

                    if ($this->typingMode && $event instanceof CharKeyEvent) {
                        $this->eventBus->emit('input', ['data' => $data]);

                        return;
                    }

                    if ($this->typingMode && $event->code === KeyCode::Esc) {
                        $this->appState->typingMode = false;

                        return;
                    }

                    $this->eventBus->emit($event);
                }
            }

            if ($data === 'q') {
                $this->close();
            }
        });
    }

    public function close(): void
    {
        $this->loop->stop();
        $this->terminal->disableRawMode();
        $this->terminal->execute(Actions::disableMouseCapture());
        $this->terminal->execute(Actions::cursorShow());
        $this->terminal->execute(Actions::alternateScreenDisable());
    }
}

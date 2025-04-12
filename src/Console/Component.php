<?php

namespace Tapper\Console;

use DI\Container;
use PhpTui\Tui\Display\Area;
use PhpTui\Tui\Widget\Widget;
use React\EventLoop\LoopInterface;
use ReflectionObject;
use Tapper\Console\CommandAttributes\FirstRender;
use Tapper\Console\CommandAttributes\KeyPressed;
use Tapper\Console\CommandAttributes\Mouse;
use Tapper\Console\CommandAttributes\OnEvent;
use Tapper\Console\CommandAttributes\Periodic;
use Tapper\Console\Commands\Command;
use Tapper\Console\State\AppState;

abstract class Component
{
    protected array $components = [];

    protected array $componentInstances = [];

    private bool $isActive = false;

    protected array $timers = [];

    protected ?Area $area = null;

    private array $firstRenders = [];

    public function __construct(
        protected readonly LoopInterface $loop,
        protected readonly EventBus $eventBus,
        protected readonly CommandInvoker $commandInvoker,
        protected readonly Container $container,
        protected readonly AppState $appState,
    ) {
        if (method_exists($this, 'init')) {
            $this->container->call([$this, 'init']);
        }

        $reflection = new ReflectionObject($this);
        $methods = $reflection->getMethods();

        foreach ($methods as $method) {
            $attributes = [
                ...$method->getAttributes(KeyPressed::class),
                ...$method->getAttributes(Mouse::class),
                ...$method->getAttributes(OnEvent::class),
            ];

            foreach ($attributes as $attribute) {
                $methodName = $method->getName();
                $attribute = $attribute->newInstance();
                $eventBus->listen(
                    $attribute->key,
                    function (array $data) use ($attribute, $methodName) {
                        if ($this->isActive || $attribute->global) {
                            $this->$methodName($data);
                        }
                    }
                );
            }

            $attributes = $method->getAttributes(Periodic::class);
            foreach ($attributes as $attribute) {
                $methodName = $method->getName();
                $attribute = $attribute->newInstance();
                $this->timers[] = $loop->addPeriodicTimer($attribute->interval, [$this, $methodName]);
            }

            $attributes = $method->getAttributes(FirstRender::class);
            foreach ($attributes as $attribute) {
                $methodName = $method->getName();
                $attribute = $attribute->newInstance();
                $this->firstRenders[] = [$this, $methodName];
            }
        }

        $this->registerComponents();

        if (method_exists($this, 'mount')) {
            $this->container->call([$this, 'mount']);
        }
    }

    private function registerComponents(): void
    {
        foreach ($this->components as $component) {
            $this->componentInstances[$component] = $this->container
                ->make($component);
        }
    }

    protected function renderComponent(string $component, Area $area): Widget
    {
        return $this->getComponent($component)->render($area);
    }

    protected function getComponent(string $component)
    {
        return $this->componentInstances[$component];
    }

    protected function execute(Command $command): mixed
    {
        return $this->commandInvoker->invoke($command);
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function render(Area $area): Widget
    {
        if ($this->area === null) {
            $this->area = $area;
            $this->callFirstRenders();
        }

        $this->area = $area;

        return $this->view($area);
    }

    abstract protected function view(Area $area): Widget;

    private function callFirstRenders(): void
    {
        foreach ($this->firstRenders as $firstRender) {
            $firstRender();
        }
    }
}

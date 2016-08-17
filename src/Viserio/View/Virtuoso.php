<?php
declare(strict_types=1);
namespace Viserio\View;

use Closure;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Contracts\View\View as ViewContract;
use Viserio\Contracts\View\Virtuoso as VirtuosoContract;
use Viserio\Support\Invoker;
use Viserio\Support\Str;
use Viserio\View\Traits\NormalizeNameTrait;

class Virtuoso implements VirtuosoContract
{
    use ContainerAwareTrait;
    use NormalizeNameTrait;

    /**
     * All of the finished, captured sections.
     *
     * @var array
     */
    protected $sections = [];

    /**
     * The stack of in-progress sections.
     *
     * @var array
     */
    protected $sectionStack = [];

    /**
     * The event manager instance.
     *
     * @var \Viserio\Contracts\Events\EventManager
     */
    protected $events;

    /**
     * The number of active rendering operations.
     *
     * @var int
     */
    protected $renderCount = 0;

    /**
     * Invoker instance.
     *
     * @var \Viserio\Support\Invoker
     */
    protected $invoker;

    /**
     * Construct.
     *
     * @param \Interop\Container\ContainerInterface  $container
     * @param \Viserio\Contracts\Events\EventManager $events
     */
    public function __construct(ContainerInterface $container, EventManagerContract $events)
    {
        $this->events = $events;

        $this->invoker = (new Invoker())
            ->injectByTypeHint(true)
            ->injectByParameterName(true)
            ->setContainer($container);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventManager(): EventManagerContract
    {
        return $this->events;
    }

    /**
     * {@inheritdoc}
     */
    public function creator($views, $callback): array
    {
        $creators = [];

        foreach ((array) $views as $view) {
            $creators[] = $this->addViewEvent($view, $callback, 'creating: ');
        }

        return $creators;
    }

    /**
     * {@inheritdoc}
     */
    public function callCreator(ViewContract $view): VirtuosoContract
    {
        $this->events->emit('creating: ' . $view->getName(), [$view]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function callComposer(ViewContract $view): VirtuosoContract
    {
        $this->events->emit('composing: ' . $view->getName(), [$view]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function composers(array $composers): array
    {
        $registered = [];

        foreach ($composers as $callback => $views) {
            $registered = array_merge($registered, $this->composer($views, $callback));
        }

        return $registered;
    }

    /**
     * {@inheritdoc}
     */
    public function composer($views, $callback, int $priority = null): array
    {
        $composers = [];

        foreach ((array) $views as $view) {
            $composers[] = $this->addViewEvent($view, $callback, 'composing: ', $priority);
        }

        return $composers;
    }

    /**
     * {@inheritdoc}
     */
    public function yieldSection(): string
    {
        if (empty($this->sectionStack)) {
            return '';
        }

        return $this->yieldContent($this->stopSection());
    }

    /**
     * {@inheritdoc}
     */
    public function yieldContent(string $section, string $default = ''): string
    {
        $sectionContent = $default;

        if (isset($this->sections[$section])) {
            $sectionContent = $this->sections[$section];
        }

        $sectionContent = str_replace(
            '@@parent',
            '--parent--holder--',
            $sectionContent
        );

        return str_replace(
            '--parent--holder--',
            '@parent',
            str_replace('@parent', '', $sectionContent)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function startSection(string $section, string $content = '')
    {
        if ($content === '') {
            if (ob_start()) {
                $this->sectionStack[] = $section;
            }
        } else {
            $this->extendSection($section, $content);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function inject(string $section, string $content)
    {
        $this->startSection($section, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function stopSection(bool $overwrite = false): string
    {
        if (empty($this->sectionStack)) {
            throw new InvalidArgumentException('Cannot end a section without first starting one.');
        }

        $last = array_pop($this->sectionStack);

        if ($overwrite) {
            $this->sections[$last] = ob_get_clean();
        } else {
            $this->extendSection($last, ob_get_clean());
        }

        return $last;
    }

    /**
     * {@inheritdoc}
     */
    public function appendSection(): string
    {
        if (empty($this->sectionStack)) {
            throw new InvalidArgumentException('Cannot end a section without first starting one.');
        }

        $last = array_pop($this->sectionStack);

        if (isset($this->sections[$last])) {
            $this->sections[$last] .= ob_get_clean();
        } else {
            $this->sections[$last] = ob_get_clean();
        }

        return $last;
    }

    /**
     * {@inheritdoc}
     */
    public function clearSections()
    {
        $this->sections = [];
        $this->sectionStack = [];
    }

    /**
     * {@inheritdoc}
     */
    public function clearSectionsIfDoneRendering()
    {
        if ($this->doneRendering()) {
            $this->clearSections();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function incrementRender()
    {
        ++$this->renderCount;
    }

    /**
     * {@inheritdoc}
     */
    public function decrementRender()
    {
        --$this->renderCount;
    }

    /**
     * {@inheritdoc}
     */
    public function doneRendering(): bool
    {
        return $this->renderCount == 0;
    }

    /**
     * {@inheritdoc}
     */
    public function hasSection(string $name): bool
    {
        return array_key_exists($name, $this->sections);
    }

    /**
     * {@inheritdoc}
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    /**
     * Append content to a given section.
     *
     * @param string $section
     * @param string $content
     */
    protected function extendSection(string $section, string $content)
    {
        if (isset($this->sections[$section])) {
            $content = str_replace('@parent', $content, $this->sections[$section]);
        }

        $this->sections[$section] = $content;
    }

    /**
     * Add an event for a given view.
     *
     * @param string          $view
     * @param \Closure|string $callback
     * @param string          $prefix
     * @param int             $priority
     *
     * @return \Closure|null
     */
    protected function addViewEvent(string $view, $callback, string $prefix = 'composing: ', int $priority = 0)
    {
        $view = $this->normalizeName($view);

        if ($callback instanceof Closure) {
            $this->events->on($prefix . $view, $callback, $priority);

            return $callback;
        } elseif (is_string($callback)) {
            return $this->addClassEvent($view, $callback, $prefix, $priority);
        }
    }

    /**
     * Register a class based view composer.
     *
     * @param string $view
     * @param string $class
     * @param string $prefix
     * @param int    $priority
     *
     * @return \Closure
     */
    protected function addClassEvent(string $view, string $class, string $prefix, int $priority = 0): Closure
    {
        $name = $prefix . $view;

        // When registering a class based view "composer", we will simply resolve the
        // classes from the application IoC container then call the compose method
        // on the instance. This allows for convenient, testable view composers.
        $callback = $this->buildClassEventCallback($class, $prefix);
        $this->events->on($name, $callback, $priority);

        return $callback;
    }

    /**
     * Build a class based container callback Closure.
     *
     * @param string $class
     * @param string $prefix
     *
     * @return \Closure
     */
    protected function buildClassEventCallback(string $class, string $prefix): Closure
    {
        list($class, $method) = $this->parseClassEvent($class, $prefix);

        // Once we have the class and method name, we can build the Closure to resolve
        // the instance out of the IoC container and call the method on it with the
        // given arguments that are passed to the Closure as the composer's data.
        return function () use ($class, $method) {
            $callable = [$this->invoker->call($class), $method];

            return call_user_func_array($callable, func_get_args());
        };
    }

    /**
     * Parse a class based composer name.
     *
     * @param string $class
     * @param string $prefix
     *
     * @return array
     */
    protected function parseClassEvent(string $class, string $prefix): array
    {
        if (Str::contains($class, '::')) {
            return explode('::', $class);
        }

        $method = Str::contains($prefix, 'composing') ? 'compose' : 'create';

        return [$class, $method];
    }
}

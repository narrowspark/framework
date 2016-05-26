<?php
namespace Viserio\View;

use Closure;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Viserio\Support\Invoker;
use Viserio\Support\Str;
use Viserio\Support\Traits\ContainerAwareTrait;
use Viserio\View\Traits\NormalizeNameTrait;

class Virtuoso
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
     * The event dispatcher instance.
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
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
     * @param ContainerInterface                                          $container
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $events
     */
    public function __construct(
        ContainerInterface $container,
        EventDispatcherInterface $events
    ) {
        $this->events = $events;

        $this->setContainer($container);

        $this->invoker = (new Invoker())
            ->injectByTypeHint(true)
            ->injectByParameterName(true)
            ->setContainer($container);
    }

    /**
     * Get the event dispatcher instance.
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function getDispatcher(): \Symfony\Component\EventDispatcher\EventDispatcherInterface
    {
        return $this->events;
    }

    /**
     * Register a view creator event.
     *
     * @param array|string    $views
     * @param \Closure|string $callback
     *
     * @return array
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
     * Call the creator for a given view.
     *
     * @param \Viserio\View\View $view
     */
    public function callCreator(View $view)
    {
        $this->events->dispatch('creating: ' . $view->getName(), new GenericEvent($view));
    }

    /**
     * Call the composer for a given view.
     *
     * @param \Viserio\View\View $view
     */
    public function callComposer(View $view)
    {
        $this->events->dispatch('composing: ' . $view->getName(), new GenericEvent($view));
    }

    /**
     * Register multiple view composers via an array.
     *
     * @param array $composers
     *
     * @return array
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
     * Register a view composer event.
     *
     * @param array|string    $views
     * @param \Closure|string $callback
     * @param int|null        $priority
     *
     * @return array
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
     * Stop injecting content into a section and return its contents.
     *
     * @return string
     */
    public function yieldSection(): string
    {
        if (empty($this->sectionStack)) {
            return '';
        }

        return $this->yieldContent($this->stopSection());
    }

    /**
     * Get the string contents of a section.
     *
     * @param string $section
     * @param string $default
     *
     * @return string
     */
    public function yieldContent($section, string $default = ''): string
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
     * Start injecting content into a section.
     *
     * @param string $section
     * @param string $content
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
     * Inject inline content into a section.
     *
     * @param string $section
     * @param string $content
     */
    public function inject(string $section, string $content)
    {
        return $this->startSection($section, $content);
    }

    /**
     * Stop injecting content into a section.
     *
     * @param bool $overwrite
     *
     * @throws \InvalidArgumentException
     *
     * @return string
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
     * Stop injecting content into a section and append it.
     *
     * @throws \InvalidArgumentException
     *
     * @return string
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
     * Flush all of the section contents.
     */
    public function flushSections()
    {
        $this->sections = [];
        $this->sectionStack = [];
    }

    /**
     * Flush all of the section contents if done rendering.
     */
    public function flushSectionsIfDoneRendering()
    {
        if ($this->doneRendering()) {
            $this->flushSections();
        }
    }

    /**
     * Increment the rendering counter.
     */
    public function incrementRender()
    {
        ++$this->renderCount;
    }

    /**
     * Decrement the rendering counter.
     */
    public function decrementRender()
    {
        --$this->renderCount;
    }

    /**
     * Check if there are no active render operations.
     *
     * @return bool
     */
    public function doneRendering(): bool
    {
        return $this->renderCount == 0;
    }

    /**
     * Check if section exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasSection($name): bool
    {
        return array_key_exists($name, $this->sections);
    }

    /**
     * Get the entire array of sections.
     *
     * @return array
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
            $this->events->addListener($prefix . $view, $callback, $priority);

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
    protected function addClassEvent(string $view, string $class, string $prefix, int $priority = 0): \Closure
    {
        $name = $prefix . $view;

        // When registering a class based view "composer", we will simply resolve the
        // classes from the application IoC container then call the compose method
        // on the instance. This allows for convenient, testable view composers.
        $callback = $this->buildClassEventCallback($class, $prefix);
        $this->events->addListener($name, $callback, $priority);

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
    protected function buildClassEventCallback(string $class, string $prefix): \Closure
    {
        list($class, $method) = $this->parseClassEvent($class, $prefix);

        // Once we have the class and method name, we can build the Closure to resolve
        // the instance out of the IoC container and call the method on it with the
        // given arguments that are passed to the Closure as the composer's data.
        return function () use ($class, $method) {
            $callable = [$this->getInvoker()->call($class), $method];

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

    /**
     * Get configured invoker.
     *
     * @return \Viserio\Support\Invoker
     */
    protected function getInvoker(): \Viserio\Support\Invoker
    {
        return $this->invoker;
    }
}

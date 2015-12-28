<?php
namespace Viserio\View;

use Closure;
use InvalidArgumentException;
use Interop\Container\ContainerInterface as ContainerInteropInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Viserio\Support\Invoker;
use Viserio\Support\Traits\ContainerAwareTrait;
use Viserio\View\Traits\NormalizeNameTrait;

class Virtuoso
{
    use ContainerAwareTrait;
    use NormalizeNameTrait;

    /**
     * The view composer events.
     *
     * @var array
     */
    protected $composers = [];

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
     * @param Interop\Container\ContainerInterface                        $container
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $events
     */
    public function __construct(
        ContainerInteropInterface $container,
        EventDispatcherInterface $events
    ) {
        $this->events = $events;

        $this->setContainer($container);

        $this->invoker = (new Invoker())
            ->injectByTypeHint(true)
            ->injectByParameterName(true)
            ->setContainer($this->getContainer());
    }

    /**
     * Get the event dispatcher instance.
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function getDispatcher()
    {
        return $this->events;
    }

    /**
     * Call the composer for a given view.
     *
     * @param  \Viserio\View\View $view
     */
    public function callComposer(View $view)
    {
        $this->events->addListener('composing: '.$view->getName(), [$view]);
    }

    /**
     * Register multiple view composers via an array.
     *
     * @param array $composers
     *
     * @return array
     */
    public function composers(array $composers)
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
    public function composer($views, $callback, $priority = null)
    {
        $composers = [];

        foreach ((array) $views as $view) {
            $composers[] = $this->addViewEvent($view, $callback, 'composing: ', $priority);
        }

        return $composers;
    }

    /**
     * Start injecting content into a section.
     *
     * @param string $section
     * @param string $content
     *
     * @return void
     */
    public function startSection($section, $content = '')
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
     * Stop injecting content into a section.
     *
     * @param  bool $overwrite
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function stopSection($overwrite = false)
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
    public function appendSection()
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
     *
     * @return void
     */
    public function incrementRender()
    {
        $this->renderCount++;
    }
    /**
     * Decrement the rendering counter.
     *
     * @return void
     */
    public function decrementRender()
    {
        $this->renderCount--;
    }
    /**
     * Check if there are no active render operations.
     *
     * @return bool
     */
    public function doneRendering()
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
    public function hasSection($name)
    {
        return array_key_exists($name, $this->sections);
    }
    /**
     * Get the entire array of sections.
     *
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * Append content to a given section.
     *
     * @param string $section
     * @param string $content
     *
     * @return void
     */
    protected function extendSection($section, $content)
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
     * @param int|null        $priority
     *
     * @return \Closure|null
     */
    protected function addViewEvent($view, $callback, $prefix = 'composing: ', $priority = null)
    {
        $view = $this->normalizeName($view);

        if ($callback instanceof Closure) {
            $this->addEventListener($prefix.$view, $callback, $priority);

            return $callback;
        } elseif (is_string($callback)) {
            return $this->addClassEvent($view, $callback, $prefix, $priority);
        }
    }

    /**
     * Register a class based view composer.
     *
     * @param string    $view
     * @param string    $class
     * @param string    $prefix
     * @param int|null  $priority
     *
     * @return \Closure
     */
    protected function addClassEvent($view, $class, $prefix, $priority = null)
    {
        $name = $prefix.$view;
        // When registering a class based view "composer", we will simply resolve the
        // classes from the application IoC container then call the compose method
        // on the instance. This allows for convenient, testable view composers.
        $callback = $this->buildClassEventCallback($class, $prefix);
        $this->addEventListener($name, $callback, $priority);

        return $callback;
    }

    /**
     * Add a listener to the event dispatcher.
     *
     * @param string   $name
     * @param \Closure $callback
     * @param int|null $priority
     *
     */
    protected function addEventListener($name, $callback, $priority = null)
    {
        if (is_null($priority)) {
            $this->events->listen($name, $callback);
        } else {
            $this->events->listen($name, $callback, $priority);
        }
    }

    /**
     * Build a class based container callback Closure.
     *
     * @param string $class
     * @param string $prefix
     *
     * @return \Closure
     */
    protected function buildClassEventCallback($class, $prefix)
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
    protected function parseClassEvent($class, $prefix)
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
    protected function getInvoker()
    {
        return $this->invoker;
    }
}

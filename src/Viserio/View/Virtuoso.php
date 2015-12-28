<?php
namespace Viserio\View;

use InvalidArgumentException;

class Virtuoso
{
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
     * Set the event dispatcher instance.
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $events
     */
    public function setDispatcher(EventDispatcherInterface $events) {
        $this->events  = $events;
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
}

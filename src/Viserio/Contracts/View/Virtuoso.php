<?php

declare(strict_types=1);
namespace Viserio\Contracts\View;

use Viserio\Contracts\Events\Dispatcher as DispatcherContract;

interface Virtuoso
{
    /**
     * Get the event dispatcher instance.
     *
     * @return \Viserio\Contracts\Events\Dispatcher
     */
    public function getDispatcher(): DispatcherContract;

    /**
     * Register a view creator event.
     *
     * @param array|string    $views
     * @param \Closure|string $callback
     *
     * @return array
     */
    public function creator($views, $callback): array;

    /**
     * Call the creator for a given view.
     *
     * @param \Viserio\Contracts\View\View $view
     *
     * @return $this
     */
    public function callCreator(View $view): Virtuoso;

    /**
     * Call the composer for a given view.
     *
     * @param \Viserio\Contracts\View\View $view
     *
     * @return $this
     */
    public function callComposer(View $view): Virtuoso;

    /**
     * Register multiple view composers via an array.
     *
     * @param array $composers
     *
     * @return array
     */
    public function composers(array $composers): array;

    /**
     * Register a view composer event.
     *
     * @param array|string    $views
     * @param \Closure|string $callback
     * @param int|null        $priority
     *
     * @return array
     */
    public function composer($views, $callback, int $priority = null): array;

    /**
     * Stop injecting content into a section and return its contents.
     *
     * @return string
     */
    public function yieldSection(): string;

    /**
     * Get the string contents of a section.
     *
     * @param string $section
     * @param string $default
     *
     * @return string
     */
    public function yieldContent(string $section, string $default = ''): string;

    /**
     * Start injecting content into a section.
     *
     * @param string $section
     * @param string $content
     */
    public function startSection(string $section, string $content = '');

    /**
     * Inject inline content into a section.
     *
     * @param string $section
     * @param string $content
     */
    public function inject(string $section, string $content);

    /**
     * Stop injecting content into a section.
     *
     * @param bool $overwrite
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function stopSection(bool $overwrite = false): string;

    /**
     * Stop injecting content into a section and append it.
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function appendSection(): string;

    /**
     * Clear all of the section contents.
     */
    public function clearSections();

    /**
     * Clear all of the section contents if done rendering.
     */
    public function clearSectionsIfDoneRendering();

    /**
     * Increment the rendering counter.
     */
    public function incrementRender();

    /**
     * Decrement the rendering counter.
     */
    public function decrementRender();

    /**
     * Check if there are no active render operations.
     *
     * @return bool
     */
    public function doneRendering(): bool;

    /**
     * Check if section exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasSection(string $name): bool;

    /**
     * Get the entire array of sections.
     *
     * @return array
     */
    public function getSections(): array;
}

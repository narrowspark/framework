<?php
namespace Viserio\Contracts\View;

interface Factory
{
    /**
     * Determine if a given view exists.
     *
     * @param string $view
     *
     * @return bool
     */
    public function exists(string $view): bool;

    /**
     * Get the evaluated view contents for the given path.
     *
     * @param string $path
     * @param array  $data
     * @param array  $mergeData
     *
     * @return \Viserio\View\View
     */
    public function file(string $path, array $data = [], array $mergeData = []): \Viserio\View\View;

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param string $view
     * @param array  $data
     * @param array  $mergeData
     *
     * @return \Viserio\View\View
     */
    public function make(string $view, array $data = [], array $mergeData = []): \Viserio\View\View;

    /**
     * Add a piece of shared data to the environment.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function share(string $key, $value = null);

    /**
     * Add a new namespace to the loader.
     *
     * @param string       $namespace
     * @param string|array $hints
     */
    public function addNamespace($namespace, $hints);
}

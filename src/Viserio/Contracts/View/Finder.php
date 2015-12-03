<?php
namespace Viserio\Contracts\View;

/**
 * Finder.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
interface Finder
{
    /**
     * Hint path delimiter value.
     *
     * @var string
     */
    const HINT_PATH_DELIMITER = '::';

    /**
     * Get the fully qualified location of the view.
     *
     * @param string $view
     *
     * @return string
     */
    public function find($view);

    /**
     * Add a location to the finder.
     *
     * @param string $location
     */
    public function addLocation($location);

    /**
     * Add a namespace hint to the finder.
     *
     * @param string       $namespace
     * @param string|array $hints
     */
    public function addNamespace($namespace, $hints);

    /**
     * Prepend a namespace hint to the finder.
     *
     * @param string       $namespace
     * @param string|array $hints
     */
    public function prependNamespace($namespace, $hints);

    /**
     * Add a valid view extension to the finder.
     *
     * @param string $extension
     */
    public function addExtension($extension);
}

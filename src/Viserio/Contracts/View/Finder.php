<?php
namespace Viserio\Contracts\View;

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
    public function find(string $view): string;

    /**
     * Add a location to the finder.
     *
     * @param string $location
     */
    public function addLocation(string $location);

    /**
     * Add a namespace hint to the finder.
     *
     * @param string       $namespace
     * @param string|array $hints
     */
    public function addNamespace(string $namespace, $hints);

    /**
     * Prepend a namespace hint to the finder.
     *
     * @param string       $namespace
     * @param string|array $hints
     */
    public function prependNamespace(string $namespace, $hints);

    /**
     * Add a valid view extension to the finder.
     *
     * @param string $extension
     */
    public function addExtension(string $extension);
}

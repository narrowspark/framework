<?php
declare(strict_types=1);
namespace Viserio\Contracts\Routing;

interface Route
{
    /**
     * Get the domain defined for the route.
     *
     * @return string|null
     */
    public function domain();

    /**
     * Get the URI that the route responds to.
     *
     * @return string
     */
    public function getUri(): string;

    /**
     * Set the URI that the route responds to.
     *
     * @param string $uri
     *
     * @return $this
     */
    public function setUri(string $uri);

    /**
     * Get the prefix of the route instance.
     *
     * @return string
     */
    public function getPrefix(): string;

    /**
     * Get the name of the route instance.
     *
     * @return string
     */
    public function getName();

    /**
     * Add or change the route name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function name(string $name);
}

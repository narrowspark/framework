<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Routing;

interface UrlGenerator
{
    /**
     * Generates an absolute URL, e.g. "http://example.com/dir/file".
     */
    const ABSOLUTE_URL = 0;
    /**
     * Generates an absolute path, e.g. "/dir/file".
     */
    const ABSOLUTE_PATH = 1;

    /**
     * Generates a relative path based on the current request path, e.g. "../parent-file".
     *
     * @see UrlGenerator::getRelativePath()
     */
    const RELATIVE_PATH = 2;

    /**
     * Generates a network path, e.g. "//example.com/dir/file".
     * Such reference reuses the current scheme but specifies the host.
     */
    const NETWORK_PATH = 3;

    /**
     * Generates a URL or path for a specific route based on the given parameters.
     *
     * @param string $name
     * @param array  $parameters
     * @param int   $referenceType
     *
     * @throws \Viserio\Component\Contracts\Routing\Exceptions\RouteNotFoundException              If the named route doesn't exist
     * @throws \Viserio\Component\Contracts\Routing\Exceptions\MissingMandatoryParametersException When some parameters are missing that are mandatory for the route
     * @throws \InvalidParameterException                                                          When a parameter value for a placeholder is not correct because
     *                                                                                             it does not match the requirement
     *
     * @return string
     */
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string;
}

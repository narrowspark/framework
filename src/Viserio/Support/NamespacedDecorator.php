<?php
namespace Viserio\Support;

use InvalidArgumentException;

abstract class NamespacedDecorator
{
    /**
     * @internal
     */
    const NAMESPACE_VERSION_KEY = 'NarrowsparkNamespaceVersion[%s]';

    /**
     * The namespace to prefix all cache ids with.
     *
     * @var string
     */
    private $namespace;

    /**
     * Constructor.
     *
     * @param string $namespace The namespace to prefix all ids with.
     *
     * @throws \InvalidArgumentException If the namespace is empty.
     */
    public function __construct($namespace)
    {
        $this->namespace = (string) $namespace;

        if ($this->namespace === '') {
            throw new InvalidArgumentException('The namespace to prefix ids with must not be empty.');
        }

        $this->namespaceVersionKey = sprintf(self::NAMESPACE_VERSION_KEY, $this->namespace);
    }

    /**
     * Retrieves the namespace that prefixes all cache ids.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Prefixes the passed id with the configured namespace value.
     *
     * @param string $id The id to namespace.
     *
     * @return string The namespaced id.
     */
    protected function getNamespacedId($id)
    {
        $namespaceVersion = $this->getNamespaceVersion();

        return sprintf('%s[%s][%s]', $this->namespace, $id, $namespaceVersion);
    }

    /**
     * Returns the namespace version.
     *
     * @return int
     */
    abstract protected function getNamespaceVersion();
}

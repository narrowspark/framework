<?php
namespace Viserio\Contracts\Connect;

interface ConnectionFactory
{
    /**
     * Get a connection instance.
     *
     * @param string $name
     *
     * @return object
     */
    public function connection($name);

    /**
     * Reconnect to the given connection.
     *
     * @param string $name
     *
     * @return object
     */
    public function reconnect($name);

    /**
     * Disconnect from the given connection.
     */
    public function disconnect();

    /**
     * Get the configuration for a connection.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function getConnectionConfig($name): array;

    /**
     * Register an extension connection resolver.
     *
     * @param Connector $name
     * @param Connector $resolver
     *
     * @return self
     */
    public function extend($name, Connector $resolver): ConnectionFactory;

    /**
     * Return all extensions.
     *
     * @return object[]
     */
    public function getExtensions(): array;

    /**
     * Return created connection.
     *
     * @return object
     */
    public function getConnection();
}

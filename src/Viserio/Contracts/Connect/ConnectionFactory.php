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
    public function connection(string $name);

    /**
     * Reconnect to the given connection.
     *
     * @param string $name
     *
     * @return object
     */
    public function reconnect(string $name);

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
    public function getConnectionConfig(string $name): array;

    /**
     * Register an extension connection resolver.
     *
     * @param string    $name
     * @param Connector $resolver
     *
     * @return self
     */
    public function extend(string $name, Connector $resolver): ConnectionFactory;

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

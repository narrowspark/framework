<?php
declare(strict_types=1);
namespace Viserio\Contracts\Database;

interface ConnectionResolver
{
    /**
     * Get a database connection instance.
     *
     * @param string|null $name
     *
     * @return \Viserio\Database\Connection
     */
    public function connection(string $name = null): \Viserio\Database\Connection;

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection(): string;

    /**
     * Set the default connection name.
     *
     * @param string $name
     */
    public function setDefaultConnection($name);
}

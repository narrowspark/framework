<?php
declare(strict_types=1);
namespace Viserio\Contracts\Database;

interface Connector
{
    /**
     * Establish a database connection.
     *
     * @param array $config
     *
     * @return \PDO
     */
    public function connect(array $config): \PDO;
}

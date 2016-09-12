<?php
declare(strict_types=1);
namespace Viserio\Database;

use Viserio\Contracts\Database\Connection as ConnectionContract;

class Connection implements ConnectionContract
{
    /**
     * The active PDO connection.
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * The active PDO connection used for reads.
     *
     * @var \PDO
     */
    protected $readPdo;

    /**
     * Create a new connection instance.
     *
     * @param string $database
     * @param string $tablePrefix
     */
    public function __construct(string $database, string $tablePrefix)
    {
        $this->config = $config;
        $this->connect = $connect;
    }
}

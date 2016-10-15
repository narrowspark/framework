<?php
declare(strict_types=1);
namespace Viserio\Database;

use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Contracts\Database\Connection as ConnectionContract;

class DatabaseManager
{
    /**
     * The config instance.
     *
     * @var \Viserio\Contracts\Config\Manager
     */
    protected $config;

    /**
     * The connect manager instance.
     *
     * @var \Viserio\Contracts\Database\Connection
     */
    protected $connect;

    /**
     * The active connection instances.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * Create a new database manager instance.
     *
     * @param \Viserio\Contracts\Config\Manager      $config
     * @param \Viserio\Contracts\Database\Connection $connect
     */
    public function __construct(ConfigContract $config, ConnectionContract $connect)
    {
        $this->config = $config;
        $this->connect = $connect;
    }
}

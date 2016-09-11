<?php
declare(strict_types=1);
namespace Viserio\Database;

use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Contracts\Connect\ConnectManager as ConnectManagerContract;

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
     * @var \Viserio\Contracts\Connect\ConnectManager
     */
    protected $connect;

    /**
     * Create a new database manager instance.
     *
     * @param \Viserio\Contracts\Config\Manager         $config
     * @param \Viserio\Contracts\Connect\ConnectManager $connect
     */
    public function __construct(ConfigContract $config, ConnectManagerContract $connect)
    {
        $this->config = $config;
        $this->connect = $connect;
    }
}

<?php
declare(strict_types=1);
namespace Viserio\Database;

use Viserio\Contracts\Config\Manager as ConfigContract;

class DatabaseManager
{
    /**
     * The application instance.
     *
     * @var \Viserio\Contracts\Config\Manager
     */
    protected $config;

    /**
     * The database connection factory instance.
     *
     * @var \Viserio\Database\Connection\ConnectionFactory
     */
    protected $factory;

    /**
     * Create a new database manager instance.
     *
     * @param \Viserio\Contracts\Config\Manager              $config
     * @param \Viserio\Database\Connection\ConnectionFactory $factory
     */
    public function __construct(ConfigContract $config, ConnectionFactory $factory)
    {
        $this->config = $config;
        $this->factory = $factory;
    }
}

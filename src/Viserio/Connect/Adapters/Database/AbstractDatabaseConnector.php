<?php
namespace Viserio\Connect\Adapters\Database;

use Narrowspark\Arr\StaticArr as Arr;
use PDO;
use PDOException;
use Viserio\Connect\Traits\DetectsLostConnections;
use Viserio\Contracts\Connect\Connector as ConnectorContract;

abstract class AbstractDatabaseConnector implements ConnectorContract
{
    use DetectsLostConnections;

    /**
     * The default PDO connection options.
     *
     * @var array
     */
    protected $options = [
        PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES  => false,
    ];

    /**
     * Get the PDO options based on the configuration.
     *
     * @param array $config
     *
     * @return array
     */
    public function getOptions(array $config)
    {
        $options = Arr::get($config, 'options', []);

        return array_diff_key($this->getDefaultOptions(), $options) + $options;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function connect(array $config);

    /**
     * Create a new PDO connection.
     *
     * @param string $dsn
     * @param array  $config
     * @param array  $options
     *
     * @throws \PDOException
     *
     * @return \PDO
     */
    public function createConnection($dsn, array $config, array $options)
    {
        try {
            $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $exception) {
            $pdo = $this->tryAgainIfCausedByLostConnection(
                $exception,
                $dsn,
                $config['username'],
                $config['password'],
                $options
            );
        }

        return $pdo;
    }

    /**
     * Get the default PDO connection options.
     *
     * @return array
     */
    public function getDefaultOptions()
    {
        return $this->options;
    }

    /**
     * Set the default PDO connection options.
     *
     * @param array $options
     */
    public function setDefaultOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Handle a exception that occurred during connect execution.
     *
     * @param \PDOException $exception
     * @param string        $dsn
     * @param string        $username
     * @param string        $password
     * @param array         $options
     *
     * @throws \PDOException
     *
     * @return \PDO
     */
    protected function tryAgainIfCausedByLostConnection(PDOException $exception, $dsn, $username, $password, $options)
    {
        if ($this->causedByLostConnection($exception)) {
            return new PDO($dsn, $username, $password, $options);
        }

        throw $exception;
    }
}

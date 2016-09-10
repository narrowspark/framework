<?php
declare(strict_types=1);
namespace Viserio\Connect\Adapters\Database;

use Mongo;
use MongoClient;
use MongoConnectionException;
use MongoDB\Client as MongoDBClient;
use Narrowspark\Arr\StaticArr as Arr;
use Throwable;
use Viserio\Connect\Traits\DetectsLostConnections;
use Viserio\Contracts\Support\Connector as ConnectorContract;

class MongoConnector implements ConnectorContract
{
    use DetectsLostConnections;

    /**
     * The default Mongo connection options.
     *
     * @var array
     */
    protected $options = [
        'connect' => true,
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

        return array_diff_key($this->options, $options) + $options;
    }

    /**
     * Establish a database connection.
     *
     * @param array $config
     *
     * @return \Mongo
     */
    public function connect(array $config)
    {
        if (isset($config['port'])) {
            $config['port'] = '27017';
        }

        return $this->createConnection(
            $this->getDsn($config),
            $config,
            $this->getOptions($config)
        );
    }

    /**
     * Create a new Mongo connection.
     *
     * @param string $dsn
     * @param array  $config
     * @param array  $options
     *
     * @return \Mongo|\MongoClient|\MongoDB\Client
     */
    public function createConnection($dsn, array $config, array $options)
    {
        try {
            $class = $this->getMongoClass();
            $mongo = new $class($dsn, $options['options']);
        } catch (MongoConnectionException $exception) {
            $mongo = $this->tryAgainIfCausedByLostConnection(
                $exception,
                $dsn,
                $options
            );
        }

        return $mongo;
    }

    /**
     * Get the default Mongo connection options.
     *
     * @return array
     */
    public function getDefaultOptions()
    {
        return $this->options;
    }

    /**
     * Set the default Mongo connection options.
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
     * @param \Throwable $exception
     * @param string     $dsn
     * @param array      $options
     *
     * @throws \MongoConnectionException
     *
     * @return \Mongo|\MongoClient|\MongoDB\Client
     */
    protected function tryAgainIfCausedByLostConnection(
        Throwable $exception,
        string $dsn,
        array $options
    ) {
        if ($this->causedByLostConnection($exception)) {
            $class = $this->getMongoClass();

            return new $class($dsn, $options);
        }

        throw $exception;
    }

    /**
     * Returns the valid Mongo class client for the current php driver.
     *
     * @return string
     */
    protected function getMongoClass(): string
    {
        if (class_exists(MongoClient::class)) {
            return MongoClient::class;
        }

        if (class_exists(MongoDBClient::class)) {
            return MongoDBClient::class;
        }

        return Mongo::class;
    }

    /**
     * Create a DSN string from a configuration.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getDsn(array $config): string
    {
        // First we will create the basic DSN setup as well as the port if it is in
        // in the configuration options. This will give us the basic DSN we will
        // need to establish the PDO connections and return them back for use.
        extract($config, EXTR_SKIP);

        if (isset($config['username'], $config['password'])) {
            return sprintf('mongodb://%s:%s@%s:%s', $username, $password, $server, $port);
        }

        return sprintf('mongodb://%s:%s', $server, $port);
    }
}

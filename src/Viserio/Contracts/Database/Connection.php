<?php
namespace Viserio\Contracts\Database;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Viserio\Contracts\Cache\Factory as CacheContract;

/**
 * Connection.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
interface Connection
{
    /**
     * Create a alias for tableprefix in query.
     *
     * @param string $table table name
     * @param string $alias alias name
     *
     * @return bool
     */
    public function setAlias($table, $alias);

    /**
     * Get alias for table.
     *
     * @param string $table
     *
     * @return string|array
     */
    public function getAlias($table);

    /**
     * Run a SQL statement and log its execution context.
     *
     * @param string   $query
     * @param array    $bindings
     * @param \Closure $callback
     *
     * @throws \Viserio\Database\Exception\ConnectException
     *
     * @return mixed
     */
    public function run($query, $bindings, \Closure $callback);

    /**
     * Set the reconnect instance on the connection.
     *
     * @param callable $reconnector
     *
     * @return $this
     */
    public function setReconnector(callable $reconnector);

    /**
     * Reconnect to the database.
     *
     *
     * @throws \LogicException
     */
    public function reconnect();

    /**
     * Execute a Closure within a transaction.
     *
     * @param \Closure $callback
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function transaction(\Closure $callback);

    /**
     * Start a new database transaction.
     */
    public function beginTransaction();

    /**
     * Commit the active database transaction.
     */
    public function commit();

    /**
     * Rollback the active database transaction.
     */
    public function rollBack();

    /**
     * Get the number of active transactions.
     *
     * @return int
     */
    public function transactionLevel();

    /**
     * Set the PDO connection.
     *
     * @param \PDO|null $pdo
     *
     * @return $this
     */
    public function setPdo($pdo);

    /**
     * Get the current PDO connection.
     *
     * @return \PDO
     */
    public function getPdo();

    /**
     * Get the database connection name.
     *
     * @return string|null
     */
    public function getName();

    /**
     * Get an option from the configuration options.
     *
     * @param string $option
     *
     * @return mixed
     */
    public function getConfig($option);

    /**
     * Get the PDO driver name.
     *
     * @return string
     */
    public function getDriverName();

    /**
     * Execute the given callback in "dry run" mode.
     *
     * @param \Closure $callback
     *
     * @return array
     */
    public function pretend(\Closure $callback);

    /**
     * Determine if the connection in a "dry run".
     *
     * @return bool
     */
    public function pretending();

    /**
     * Log a query in the connection's query log.
     *
     * @param string     $query
     * @param array      $bindings
     * @param float|null $time
     */
    public function logQuery($query, $bindings, $time = null);

    /**
     * Get the connection query log.
     *
     * @return array
     */
    public function getQueryLog();

    /**
     * Clear the query log.
     */
    public function flushQueryLog();

    /**
     * Enable the query log on the connection.
     */
    public function enableQueryLog();

    /**
     * Disable the query log on the connection.
     */
    public function disableQueryLog();

    /**
     * Determine whether we're logging queries.
     *
     * @return bool
     */
    public function logging();

    /**
     * Get the name of the connected database.
     *
     * @return string
     */
    public function getDatabaseName();

    /**
     * Set the name of the connected database.
     *
     * @param string $database
     *
     * @return string
     */
    public function setDatabaseName($database);

    /**
     * Get the table prefix for the connection.
     *
     * @return string
     */
    public function getTablePrefix();

    /**
     * Set the table prefix in use by the connection.
     *
     * @param string $prefix
     */
    public function setTablePrefix($prefix);

    /**
     * Get the cache manager instance.
     *
     * @return \Viserio\Cache\CacheManager|\Closure
     */
    public function getCacheManager();

    /**
     * Set the cache manager instance on the connection.
     *
     * @param \Viserio\Contracts\Cache\Factory $cache
     */
    public function setCacheManager(CacheContract $cache);
}

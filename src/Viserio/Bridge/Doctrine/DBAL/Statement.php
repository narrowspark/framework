<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\DBAL;

use Doctrine\DBAL\Driver\Statement as DriverStatement;
use Narrowspark\Collection\Collection;

class Statement
{
    /**
     * Instance of doctrine statement.
     *
     * @var \Doctrine\DBAL\Driver\Statement
     */
    protected $statement;

    /**
     * Create a new Statement instance.
     *
     * @param \Doctrine\DBAL\Driver\Statement $statement
     */
    public function __construct(DriverStatement $statement)
    {
        $this->statement = $statement;
    }

    /**
     * Invoke doctrine statement functions.
     *
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($name, $args)
    {
        return \call_user_func_array([$this->statement, $name], $args);
    }

    /**
     * Returns the next row of a result set.
     *
     * @param null|int $fetchMode Controls how the next row will be returned to the caller.
     *                            The value must be one of the PDO::FETCH_* constants,
     *                            defaulting to PDO::FETCH_BOTH.
     *
     * @return null|bool|float|int|object|string The return value of this method on
     *                                           success depends on the fetch mode.
     *                                           In all cases, FALSE is returned on failure.
     */
    public function fetch(int $fetchMode = null)
    {
        $stmt = $this->statement->fetch($fetchMode);

        if (\is_array($stmt)) {
            return new Collection($stmt);
        }

        return $stmt;
    }

    /**
     * Returns an array containing all of the result set rows.
     *
     * @param null|int $fetchMode Controls how the next row will be returned to the caller.
     *                            The value must be one of the PDO::FETCH_* constants,
     *                            defaulting to PDO::FETCH_BOTH.
     *
     * @return \Narrowspark\Collection\Collection
     */
    public function fetchAll(int $fetchMode = null): Collection
    {
        return new Collection($this->statement->fetch($fetchMode));
    }
}

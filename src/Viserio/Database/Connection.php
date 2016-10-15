<?php
declare(strict_types=1);
namespace Viserio\Database;

use Closure;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection as DoctrineConnection;
use Narrowspark\Collection\Collection;

class Connection extends DoctrineConnection
{
    /**
     * {@inheritdoc}
     *
     * @return \Narrowspark\Collection\Collection|bool
     */
    public function fetchAssoc($statement, array $params = [], array $types = [])
    {
        $stmt = parent::fetchAssoc($statement, $params, $types);

        if (is_array($stmt)) {
            return new Collection($stmt);
        }

        return $stmt;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Narrowspark\Collection\Collection|bool
     */
    public function fetchArray($statement, array $params = [], array $types = [])
    {
        $stmt = parent::fetchArray($statement, $params, $types);

        if (is_array($stmt)) {
            return new Collection($stmt);
        }

        return $stmt;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Narrowspark\Collection\Collection
     */
    public function fetchAll($sql, array $params = [], $types = [])
    {
        return new Collection(parent::fetchAll($sql, $params, $types));
    }

    /**
     * {@inheritdoc}
     *
     * @return \Narrowspark\Collection\Collection
     */
    public function project($query, array $params, Closure $function)
    {
        return new Collection(parent::project($query, $params, $function));
    }

    /**
     * {@inheritdoc}
     *
     * @return \Viserio\Database\Statement
     */
    public function prepare($statement)
    {
        return new Statement(parent::prepare($statement));
    }

    /**
     * {@inheritdoc}
     *
     * @return \Viserio\Database\Statement
     */
    public function executeQuery($query, array $params = [], $types = [], QueryCacheProfile $qcp = null)
    {
        return new Statement(parent::executeQuery($query, $params, $types, $qcp));
    }

    /**
     * {@inheritdoc}
     *
     * @return \Viserio\Database\Statement
     */
    public function query(...$args)
    {
        return new Statement(parent::query());
    }
}

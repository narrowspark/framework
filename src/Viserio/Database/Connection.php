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
     */
    public function fetchArray($statement, array $params = [], array $types = [])
    {
        return new Collection(parent::fetchArray($statement, $params, $types));
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll($sql, array $params = [], $types = [])
    {
        return new Collection(parent::fetchAll($sql, $params, $types));
    }

    /**
     * {@inheritdoc}
     */
    public function project($query, array $params, Closure $function)
    {
        return new Collection(parent::project($query, $params, $function));
    }

    /**
     * {@inheritdoc}
     */
    public function prepare($statement)
    {
        return new Statement(parent::prepare($statement));
    }

    /**
     * {@inheritdoc}
     */
    public function executeQuery($query, array $params = [], $types = [], QueryCacheProfile $qcp = null)
    {
        return new Statement(parent::executeQuery($query, $params, $types, $qcp));
    }

    /**
     * {@inheritdoc}
     */
    public function query(...$args)
    {
        return new Statement(parent::query());
    }
}

<?php
declare(strict_types=1);
namespace Viserio\Database;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection as DoctrineConnection;

class Connection extends DoctrineConnection
{
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
        return new Statement(parent::query(...$args));
    }
}

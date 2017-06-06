<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\DBAL;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection as DoctrineConnection;

class Connection extends DoctrineConnection
{
    /**
     * {@inheritdoc}
     *
     * @return \Viserio\Bridge\Doctrine\Statement
     */
    public function prepare($statement): Statement
    {
        return new Statement(parent::prepare($statement));
    }

    /**
     * {@inheritdoc}
     *
     * @return \Viserio\Bridge\Doctrine\Statement
     */
    public function executeQuery($query, array $params = [], $types = [], QueryCacheProfile $qcp = null): Statement
    {
        return new Statement(parent::executeQuery($query, $params, $types, $qcp));
    }

    /**
     * {@inheritdoc}
     *
     * @return \Viserio\Bridge\Doctrine\Statement
     */
    public function query(...$args): Statement
    {
        return new Statement(parent::query(...$args));
    }
}

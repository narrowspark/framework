<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing\DBAL;

use Viserio\Bridge\Doctrine\DBAL\ConnectionManager;

class StaticConnectionManager extends ConnectionManager
{
    /**
     * {@inheritdoc}
     */
    public function getConnection(?string $name = null)
    {
        $connectionOriginalDriver = parent::getConnection($name);
        $connectionWrapperClass   = \get_class($connectionOriginalDriver);

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = new $connectionWrapperClass(
            $connectionOriginalDriver->getParams(),
            new StaticDriver($connectionOriginalDriver->getDriver(), $connectionOriginalDriver->getDatabasePlatform()),
            $connectionOriginalDriver->getConfiguration(),
            $connectionOriginalDriver->getEventManager()
        );

        if (StaticDriver::isKeepStaticConnections()) {
            // The underlying connection already has a transaction started.
            // Make sure we use savepoints to be able to easily roll-back nested transactions
            if ($connection->getDriver()->getDatabasePlatform()->supportsSavepoints()) {
                $connection->setNestTransactionsWithSavepoints(true);
            }

            // We start a transaction on the connection as well
            // so the internal state ($_transactionNestingLevel) is in sync with the underlying connection.
            $connection->beginTransaction();
        }

        return $connection;
    }
}

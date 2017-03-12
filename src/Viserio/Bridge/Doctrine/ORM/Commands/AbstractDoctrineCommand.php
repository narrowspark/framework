<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Commands;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Sharding\PoolingShardConnection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\EntityGenerator;
use LogicException;
use Viserio\Component\Console\Command\Command;

abstract class AbstractDoctrineCommand extends Command
{
    /**
     * get a doctrine entity generator.
     *
     * @return \Doctrine\ORM\Tools\EntityGenerator
     */
    protected function getEntityGenerator(): EntityGenerator
    {
        $entityGenerator = new EntityGenerator();
        $entityGenerator->setGenerateAnnotations(false);
        $entityGenerator->setGenerateStubMethods(true);
        $entityGenerator->setRegenerateEntityIfExists(false);
        $entityGenerator->setUpdateEntityIfExists(true);
        $entityGenerator->setNumSpaces(4);
        $entityGenerator->setAnnotationPrefix('ORM\\');

        return $entityGenerator;
    }

    /**
     * Get a doctrine entity manager by symfony name.
     *
     * @param string   $name
     * @param null|int $shardId
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager(string $name, ?int $shardId = null): EntityManager
    {
        $manager = $this->getContainer()->get('doctrine')->getManager($name);

        if ($shardId) {
            if (! $manager->getConnection() instanceof PoolingShardConnection) {
                throw new LogicException(sprintf("Connection of EntityManager '%s' must implement shards configuration.", $name));
            }

            $manager->getConnection()->connect($shardId);
        }

        return $manager;
    }

    /**
     * Get a doctrine dbal connection by symfony name.
     *
     * @param string $name
     *
     * @return \Doctrine\DBAL\Connection
     */
    protected function getDoctrineConnection(string $name): Connection
    {
        return $this->getContainer()->get('doctrine')->getConnection($name);
    }
}

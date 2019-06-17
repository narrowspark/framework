<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing\Concerns;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\Assert;

trait InteractsWithEntities
{
    /**
     * Check if a entity exists.
     *
     * @param string $class
     * @param mixed  $id
     *
     * @return object
     */
    public static function entityExists(string $class, $id): object
    {
        $entity = self::entityManager()->find($class, $id);

        Assert::assertNotNull(
            $entity,
            \sprintf(
                'A [%s] entity was not found by id: %s',
                $class,
                \print_r($id, true)
            )
        );

        return $entity;
    }

    /**
     * Check if a entitie dont exists.
     *
     * @param string $class
     * @param mixed  $id
     *
     * @return void
     */
    public static function entityDoesNotExist(string $class, $id): void
    {
        Assert::assertNull(
            self::entityManager()->find($class, $id),
            \sprintf(
                'A [%s] entity was found by id: %s',
                $class,
                \print_r($id, true)
            )
        );
    }

    /**
     * Check if entities match.
     *
     * @param string   $class
     * @param array    $criteria
     * @param null|int $count
     *
     * @return object[]
     */
    public static function entitiesMatch(string $class, array $criteria, ?int $count = null): array
    {
        $entities = self::entityManager()->getRepository($class)->findBy($criteria);

        Assert::assertNotEmpty(
            $entities,
            \sprintf(
                'No [%s] entities were found with the given criteria: %s',
                $class,
                \print_r($criteria, true)
            )
        );

        if ($count !== null) {
            Assert::assertCount(
                $count,
                $entities,
                \sprintf(
                    'Expected to find %s [%s] entities, but found %s with the given criteria: %s',
                    $count,
                    $class,
                    \count($entities),
                    \print_r($criteria, true)
                )
            );
        }

        return $entities;
    }

    /**
     * Check if entities does not match.
     *
     * @param string $class
     * @param array  $criteria
     *
     * @return void
     */
    public static function noEntitiesMatch(string $class, array $criteria): void
    {
        Assert::assertEmpty(
            self::entityManager()->getRepository($class)->findBy($criteria),
            "Some [${class}] entities were found with the given criteria: " . \print_r($criteria, true)
        );
    }

    /**
     *  Create a new EntityManager class.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    abstract protected static function entityManager(): EntityManager;
}

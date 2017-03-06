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
     * @throws \PHPUnit_Framework_AssertionFailedError
     *
     * @return object
     */
    public function entityExists(string $class, $id)
    {
        $entity = $this->entityManager()->find($class, $id);

        Assert::assertNotNull(
            $entity,
            sprintf(
                "A [%s] entity was not found by id: %s",
                $class,
                print_r($id, true)
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
     * @throws \PHPUnit_Framework_AssertionFailedError
     *
     * @return void
     */
    public function entityDoesNotExist(string $class, $id): void
    {
        Assert::assertNull(
            $this->entityManager()->find($class, $id),
            sprintf(
                "A [%s] entity was found by id: %s",
                $class,
                print_r($id, true)
            )
        );
    }

    /**
     * Check if entities match.
     *
     * @param string   $class
     * @param array    $criteria
     * @param int|null $count
     *
     * @throws \PHPUnit_Framework_AssertionFailedError
     *
     * @return object[]
     */
    public function entitiesMatch(string $class, array $criteria, ?int $count = null): array
    {
        $entities = $this->entityManager()->getRepository($class)->findBy($criteria);

        Assert::assertNotEmpty(
            $entities,
            sprintf(
                "No [%s] entities were found with the given criteria: %s",
                $class,
                print_r($criteria, true)
            )
        );

        if ($count !== null) {
            Assert::assertCount(
                $count,
                $entities,
                sprintf(
                    'Expected to find %s [%s] entities, but found %s with the given criteria: %s',
                    $count,
                    $class,
                    count($entities),
                    print_r($criteria, true)
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
     * @throws \PHPUnit_Framework_AssertionFailedError
     *
     * @return void
     */
    public function noEntitiesMatch(string $class, array $criteria): void
    {
        Assert::assertEmpty(
            $this->entityManager()->getRepository($class)->findBy($criteria),
            "Some [$class] entities were found with the given criteria: " . print_r($criteria, true)
        );
    }

    /**
     *  Create a new EntityManager class.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    abstract protected function entityManager(): EntityManager;
}

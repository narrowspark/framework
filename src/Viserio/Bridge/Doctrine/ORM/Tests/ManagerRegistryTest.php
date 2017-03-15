<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Tests;

use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Bridge\Doctrine\ORM\EntityManagerFactory;
use Viserio\Bridge\Doctrine\ORM\ManagerRegistry;

class ManagerRegistryTest extends MockeryTestCase
{
    /**
     * @var Mock
     */
    protected $container;

    /**
     * @var Mock
     */
    protected $factory;

    /**
     * @var \Viserio\Bridge\Doctrine\ORM\ManagerRegistry
     */
    protected $registry;

    public function setUp()
    {
        parent::setUp();

        $this->container = $this->mock(ContainerInterface::class);
        $this->factory   = $this->mock(EntityManagerFactory::class);

        $this->registry = new ManagerRegistry(
            $this->container,
            $this->factory
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Doctrine Connection named [non-existing] does not exist.
     */
    public function test_cannot_non_existing_connection()
    {
        $this->registry->getConnection('non-existing');
    }
}

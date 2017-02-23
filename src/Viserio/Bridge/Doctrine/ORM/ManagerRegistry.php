<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry as BaseManagerRegistry;
use Interop\Container\ContainerInterface;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;

final class ManagerRegistry implements BaseManagerRegistry
{
    use ContainerAwareTrait;

    /**
     * @const
     */
    public const MANAGER_BINDING_PREFIX = 'doctrine.managers.';

    /**
     * @const
     */
    public const CONNECTION_BINDING_PREFIX = 'doctrine.connections.';

    /**
     * @var string
     */
    protected $defaultManager = 'default';

    /**
     * @var string
     */
    protected $defaultConnection = 'default';

    /**
     * @var \Viserio\Bridge\Doctrine\EntityManagerFactory
     */
    protected $factory;

    /**
     * @var array
     */
    protected $managers = [];

    /**
     * @var array
     */
    protected $connections = [];

    /**
     * @var array
     */
    protected $managersMap = [];

    /**
     * @var array
     */
    protected $connectionsMap = [];

    /**
     * Create a new manager registry.
     *
     * @param \Interop\Container\ContainerInterface         $container
     * @param \Viserio\Bridge\Doctrine\EntityManagerFactory $factory
     */
    public function __construct(ContainerInterface $container, EntityManagerFactory $factory)
    {
        $this->container = $container;
        $this->factory   = $factory;
    }
}

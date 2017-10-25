<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM;

use Doctrine\Common\Persistence\ManagerRegistry as BaseManagerRegistry;
use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\ORMException;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use ReflectionClass;
use Viserio\Component\Contract\Container\Traits\ContainerAwareTrait;

final class ManagerRegistry implements BaseManagerRegistry
{
    use ContainerAwareTrait;

    /**
     * @var string MANAGER_BINDING_PREFIX
     */
    public const MANAGER_BINDING_PREFIX = 'doctrine.managers.';

    /**
     * @var string CONNECTION_BINDING_PREFIX
     */
    public const CONNECTION_BINDING_PREFIX = 'doctrine.connections.';

    /**
     * The default name for manager.
     *
     * @var string
     */
    protected $defaultManager = 'default';

    /**
     * The default name for connection.
     *
     * @var string
     */
    protected $defaultConnection = 'default';

    /**
     * A EntityManagerFactory instance.
     *
     * @var \Viserio\Bridge\Doctrine\EntityManagerFactory
     */
    protected $factory;

    /**
     * A list of all managers.
     *
     * @var array
     */
    protected $managers = [];

    /**
     * A list of all connections.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * Mapping of managers.
     *
     * @var array
     */
    protected $managersMap = [];

    /**
     * Mapping of connections.
     *
     * @var array
     */
    protected $connectionsMap = [];

    /**
     * Create a new manager registry instance.
     *
     * @param \Interop\Container\ContainerInterface         $container
     * @param \Viserio\Bridge\Doctrine\EntityManagerFactory $factory
     */
    public function __construct(ContainerInterface $container, EntityManagerFactory $factory)
    {
        $this->container = $container;
        $this->factory   = $factory;
    }

    /**
     * Set a default manager.
     *
     * @param string $defaultManager
     *
     * @return void
     */
    public function setDefaultManager(string $defaultManager): void
    {
        $this->defaultManager = $defaultManager;
    }

    /**
     * Add a new manager instance.
     *
     * @param string $manager
     * @param array  $settings
     *
     * @return void
     */
    public function addManager(string $manager, array $settings = []): void
    {
        $this->container->singleton($this->getManagerBindingName($manager), function () use ($settings) {
            return $this->factory->create($settings);
        });

        $this->managers[$manager] = $manager;

        $this->addConnection($manager, $settings);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultManagerName(): string
    {
        if (isset($this->managers[$this->defaultManager])) {
            return $this->defaultManager;
        }

        return head($this->managers);
    }

    /**
     * {@inheritdoc}
     */
    public function getManager($name = null): ObjectManager
    {
        $name = $name ?? $this->getDefaultManagerName();

        if (! $this->hasManager($name)) {
            throw new InvalidArgumentException(sprintf('Doctrine Manager named [%s] does not exist.', $name));
        }

        if (isset($this->managersMap[$name])) {
            return $this->managersMap[$name];
        }

        return $this->managersMap[$name] = $this->getService(
            $this->getManagerBindingName($this->managers[$name])
        );
    }

    /**
     * Check if a manager exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasManager(string $name): bool
    {
        return isset($this->managers[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getManagerNames(): array
    {
        return $this->managers;
    }

    /**
     * {@inheritdoc}
     */
    public function getManagers(): array
    {
        $managers = [];

        foreach ($this->getManagerNames() as $name) {
            $managers[$name] = $this->getManager($name);
        }

        return $managers;
    }

    /**
     * {@inheritdoc}
     */
    public function resetManager($name = null): ObjectManager
    {
        $name = $name ?? $this->getDefaultManagerName();

        if (! $this->hasManager($name)) {
            throw new InvalidArgumentException(sprintf('Doctrine Manager named [%s] does not exist.', $name));
        }

        // force the creation of a new document manager
        // if the current one is closed
        $this->resetService(
            $this->getManagerBindingName($this->managers[$name])
        );

        $this->resetService(
            $this->getConnectionBindingName($this->connections[$name])
        );

        unset($this->managersMap[$name], $this->connectionsMap[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAliasNamespace($alias): string
    {
        foreach ($this->getManagerNames() as $name) {
            try {
                return $this->getManager($name)->getConfiguration()->getEntityNamespace($alias);
            } catch (ORMException $e) {
            }
        }

        throw ORMException::unknownEntityNamespace($alias);
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository($persistentObject, $persistentManagerName = null): ObjectRepository
    {
        return $this->getManager($persistentManagerName)->getRepository($persistentObject);
    }

    /**
     * {@inheritdoc}
     */
    public function getManagerForClass($class): ?ObjectManager
    {
        // Check for namespace alias
        if (mb_strpos($class, ':') !== false) {
            list($namespaceAlias, $simpleClassName) = explode(':', $class, 2);
            $class                                  = $this->getAliasNamespace($namespaceAlias) . '\\' . $simpleClassName;
        }

        $proxyClass = new ReflectionClass($class);

        if ($proxyClass->implementsInterface(Proxy::class)) {
            $class = $proxyClass->getParentClass()->getName();
        }

        foreach ($this->getManagerNames() as $name) {
            $manager = $this->getManager($name);

            if (! $manager->getMetadataFactory()->isTransient($class)) {
                foreach ($manager->getMetadataFactory()->getAllMetadata() as $metadata) {
                    if ($metadata->getName() === $class) {
                        return $manager;
                    }
                }
            }
        }
    }

    /**
     * Set a default connection.
     *
     * @param string $defaultConnection
     *
     * @return void
     */
    public function setDefaultConnection(string $defaultConnection): void
    {
        $this->defaultConnection = $defaultConnection;
    }

    /**
     * Add a new connection.
     *
     * @param string|object $connection
     * @param array         $settings
     *
     * @return void
     */
    public function addConnection($connection, array $settings = []): void
    {
        $this->container->singleton($this->getConnectionBindingName($connection), function () use ($connection) {
            return $this->getManager($connection)->getConnection();
        });

        $this->connections[$connection] = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConnectionName(): string
    {
        if (isset($this->connections[$this->defaultConnection])) {
            return $this->defaultConnection;
        }

        return reset($this->connections);
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection($name = null)
    {
        $name = $name ?? $this->getDefaultConnectionName();

        if (! $this->hasConnection($name)) {
            throw new InvalidArgumentException(sprintf('Doctrine Connection named [%s] does not exist.', $name));
        }

        if (isset($this->connectionsMap[$name])) {
            return $this->connectionsMap[$name];
        }

        return $this->connectionsMap[$name] = $this->getService(
            $this->getConnectionBindingName($this->connections[$name])
        );
    }

    /**
     * Check if a connection exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasConnection(string $name): bool
    {
        return isset($this->connections[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getConnections(): array
    {
        $connections = [];

        foreach ($this->connections as $name) {
            $connections[$name] = $this->getConnection($name);
        }

        return $connections;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionNames(): array
    {
        return $this->connections;
    }

    /**
     * Prefix a manager name.
     *
     * @param string $manager
     *
     * @return string
     */
    protected function getManagerBindingName(string $manager): string
    {
        return self::MANAGER_BINDING_PREFIX . $manager;
    }

    /**
     * Prefix a connection name.
     *
     * @param $connection
     *
     * @return string
     */
    protected function getConnectionBindingName(string $connection): string
    {
        return self::CONNECTION_BINDING_PREFIX . $connection;
    }

    /**
     * {@inheritdoc}
     */
    protected function getService($name)
    {
        return $this->container->get($name);
    }
}

<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Resolvers;

use InvalidArgumentException;
use Interop\Container\ContainerInterface;
use Doctrine\ORM\Mapping\EntityListenerResolver as ResolverContract;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;

class EntityListenerResolver implements ResolverContract
{
    use ContainerAwareTrait;

    /**
     * Map of class name to entity listener instances.
     *
     * @var object[]
     */
    private $instances = [];

    /**
     * Create a new entity listener resolver instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function clear($className = null)
    {
        if ($className) {
            unset($this->instances[$className = trim($className, '\\')]);
            return;
        }

        $this->instances = [];
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($className)
    {
        if (isset($this->instances[$className = trim($className, '\\')])) {
            return $this->instances[$className];
        }

        return $this->instances[$className] = $this->getContainer()->get($className);
    }

    /**
     * {@inheritdoc}
     */
    public function register($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException(sprintf('An object was expected, but got "%s".', gettype($object)));
        }

        $this->instances[get_class($object)] = $object;
    }
}

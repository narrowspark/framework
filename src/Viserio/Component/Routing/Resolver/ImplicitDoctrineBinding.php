<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Resolver;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityNotFoundException;
use ReflectionFunction;
use ReflectionMethod;
use Viserio\Component\Contracts\Routing\ImplicitBinding as ImplicitBindingContract;
use Viserio\Component\Contracts\Routing\Registrar as RegistrarContract;

class ImplicitDoctrineBinding implements ImplicitBindingContract
{
    /**
     * The router instance.
     *
     * @var Registrar
     */
    protected $router;

    /**
     * The doctrine persistence instance.
     *
     * @var \Doctrine\Common\Persistence\ManagerRegistry
     */
    protected $registry;

    /**
     * @param \Viserio\Component\Contracts\Routing\Registrar $router
     * @param \Doctrine\Common\Persistence\ManagerRegistry   $registry
     */
    public function __construct(RegistrarContract $router, ManagerRegistry $registry)
    {
        $this->router   = $router;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Route $route): Route
    {
        $parameters = $route->getParameters();
        $action     = $route->getAction();

        foreach ($this->getParameters($action['uses']) as $parameter) {
            if (! array_key_exists($parameter->name, $parameters)) {
                continue;
            }

            // Make sure this parameter is a class.
            if (! $parameter->getClass()) {
                continue;
            }

            $class = $parameter->getClass()->getName();

            // Try to find the entity manager for the given class.
            if (is_null($entityManager = $this->registry->getManagerForClass($class))) {
                continue;
            }

            // Find the entity by route parameter value.
            $entity = $entityManager->find($class, $parameters[$parameter->name]);

            // When no entity is found check if the route accepts an empty entity.
            if (is_null($entity) && ! $parameter->isDefaultValueAvailable()) {
                throw new EntityNotFoundException(sprintf('No query results for entity [%s]', $class));
            }

            $route->setParameter($parameter->name, $entity);
        }
    }

    /**
     * Reflect the parameters of the method or function of the route.
     *
     * @param string|callable $uses
     *
     * @return \ReflectionParameter[]
     */
    protected function getParameters($uses): array
    {
        if (is_string($uses)) {
            list($class, $method) = explode('@', $uses);

            return (new ReflectionMethod($class, $method))->getParameters();
        }

        return (new ReflectionFunction($uses))->getParameters();
    }
}

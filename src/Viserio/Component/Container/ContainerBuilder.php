<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Container;

use Closure;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use Traversable;
use Viserio\Component\Container\Definition\AliasDefinition;
use Viserio\Component\Container\Definition\ArrayDefinition;
use Viserio\Component\Container\Definition\ClosureDefinition;
use Viserio\Component\Container\Definition\FactoryDefinition;
use Viserio\Component\Container\Definition\IteratorDefinition;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Container\Definition\ParameterDefinition;
use Viserio\Component\Container\Definition\UndefinedDefinition;
use Viserio\Component\Container\Traits\ReflectorTrait;
use Viserio\Contract\Container\Argument\Argument as ArgumentContract;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\AliasDefinition as AliasDefinitionContract;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;
use Viserio\Contract\Container\Definition\TagAwareDefinition as TagAwareDefinitionContract;
use Viserio\Contract\Container\Exception\CircularDependencyException;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\Exception\LogicException;
use Viserio\Contract\Container\Exception\NotFoundException;
use Viserio\Contract\Container\Factory as FactoryContract;
use Viserio\Contract\Container\Pipe as PipeContract;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ExtendServiceProvider as ExtendServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\PipelineServiceProvider as PipelineServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Container\ServiceReferenceGraph as ServiceReferenceGraphContract;
use Viserio\Contract\Container\TaggedContainer as TaggedContainerContract;

final class ContainerBuilder implements ContainerBuilderContract
{
    use ReflectorTrait {
        getClassReflector as protectedGetClassReflector;
        getMethodReflector as protectedGetMethodReflector;
        getFunctionReflector as protectedGetFunctionReflector;
    }

    /**
     * The registered type aliases.
     *
     * @var \Viserio\Contract\Container\Definition\AliasDefinition[]
     */
    private $aliases = [];

    /**
     * The container's definitions.
     *
     * @var ArrayDefinition[]|ClosureDefinition[]|DefinitionContract[]|FactoryDefinition[]|IteratorDefinition[]|ObjectDefinition[]|UndefinedDefinition[]
     */
    private $definitions = [];

    /**
     * The container's parameters.
     *
     * @var array
     */
    private $parameters = [];

    /**
     * All of the used tags.
     *
     * @var array
     */
    private $usedTags = [];

    /**
     * The extension closures for services.
     *
     * @var array
     */
    private $extenders = [];

    /**
     * Whether the container has already been built.
     *
     * @var bool
     */
    private $locked = false;

    /**
     * Removed definition ids.
     *
     * @var array
     */
    private $removedIds = [];

    /**
     * A dependency graph instance.
     *
     * @var \Viserio\Contract\Container\ServiceReferenceGraph
     */
    private $serviceReferenceGraph;

    /**
     * A dependency graph instance.
     *
     * @var \Viserio\Component\Container\PipelineConfig
     */
    private $pipelineConfig;

    /** @var array */
    private $logs = [];

    /**
     * Create a new container builder instance.
     */
    public function __construct()
    {
        $this->serviceReferenceGraph = new ServiceReferenceGraph();
        $this->pipelineConfig = new PipelineConfig();

        $this->singleton(ContainerInterface::class, $this);
        $this->setAlias(ContainerInterface::class, FactoryContract::class);
        $this->setAlias(ContainerInterface::class, TaggedContainerContract::class);
        $this->setAlias(ContainerInterface::class, 'container');
    }

    /**
     * Clone method.
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    private function __clone()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * {@inheritdoc}
     */
    public function setAliases(array $aliases): void
    {
        $this->aliases = $aliases;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefinitions(array $definitions): void
    {
        $this->definitions = [];

        $this->addDefinitions($definitions);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtenders(string $abstract): array
    {
        return $this->extenders[\array_key_exists($abstract, $this->aliases) ? $this->aliases[$abstract]->getName() : $abstract] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getRemovedIds(): array
    {
        return $this->removedIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceReferenceGraph(): ServiceReferenceGraphContract
    {
        return $this->serviceReferenceGraph;
    }

    /**
     * Returns the PipelineConfig instance.
     *
     * @internal
     *
     * @return \Viserio\Component\Container\PipelineConfig
     */
    public function getPipelineConfig(): PipelineConfig
    {
        return $this->pipelineConfig;
    }

    /**
     * Returns the log.
     *
     * @internal
     *
     * @return array
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * {@inheritdoc}
     */
    public function addDefinitions(array $definitions): void
    {
        foreach ($definitions as $id => $definition) {
            $this->setDefinition($id, $definition);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefinition(string $id, DefinitionContract $definition): void
    {
        if ('' === $id || \strlen($id) !== \strcspn($id, "\0\r\n'")) {
            throw new InvalidArgumentException(\sprintf('Invalid service id: "%s"', $id));
        }

        unset($this->aliases[$id], $this->removedIds[$id]);

        $this->definitions[$id] = $definition;
    }

    /**
     * Get a hash from the given value.
     *
     * @internal
     *
     * @param string $name
     *
     * @return string
     */
    public static function getHash(string $name): string
    {
        $salt = "pW;RK!~D)@C*?FVg[O6-#rsld_tF=c`!8A&x7:Q?c3='<O#jp\$U Vohg0,BO Xzv";

        // note: this code is mathematically buggy by default, as we are using a hash to identify
        //       cache entries. The string length is added to further reduce likeliness (although
        //       already imperceptible) of key collisions.
        //       In the "real world", this code will work just fine.
        return \hash('sha256', \str_replace('\\', '__', $name) . ':' . \strlen($name) . $salt);
    }

    /**
     * Returns the Service Conditionals.
     *
     * @param mixed $value An array of conditionals to return
     *
     * @return array An array of Service conditionals
     *
     * @internal
     */
    public static function getServiceConditionals($value): array
    {
        $services = [];

        if (\is_array($value)) {
            foreach ($value as $v) {
                $services = \array_unique(\array_merge($services, self::getServiceConditionals($v)));
            }
        } elseif ($value instanceof ReferenceDefinitionContract && 3/* ReferenceDefinitionContract::IGNORE_ON_INVALID_REFERENCE */ === $value->getBehavior()) {
            $services[] = $value->getName();
        }

        return $services;
    }

    /**
     * Returns the initialized conditionals.
     *
     * @param mixed $value
     *
     * @return array An array of uninitialized conditionals
     *
     * @internal
     */
    public static function getInitializedConditionals($value): array
    {
        $services = [];

        if (\is_array($value)) {
            foreach ($value as $v) {
                $services = \array_unique(\array_merge($services, self::getInitializedConditionals($v)));
            }
        } elseif ($value instanceof ReferenceDefinitionContract && 2/* ReferenceDefinitionContract::IGNORE_ON_UNINITIALIZED_REFERENCE */ === $value->getBehavior()) {
            $services[] = $value->getName();
        }

        return $services;
    }

    /**
     * Clear the container of all services and resolved instances.
     *
     * @return void
     */
    public function reset(): void
    {
        $this->definitions = $this->aliases = $this->extenders = $this->parameters = [];
    }

    /**
     * {@inheritdoc}
     *
     * @return ArrayDefinition|ClosureDefinition|FactoryDefinition|IteratorDefinition|ObjectDefinition|UndefinedDefinition
     */
    public function bind(string $abstract, $concrete = null)
    {
        $this->checkName($abstract);

        // Drop all of the stale instance and alias.
        unset($this->definitions[$abstract], $this->aliases[$abstract]);

        // If no concrete type was given, we will simply set the concrete type to the abstract type.
        return $this->definitions[$abstract] = self::createDefinition($abstract, (is_class($abstract) || \interface_exists($abstract)) ? $concrete ?? $abstract : $concrete, 1 /* DefinitionContract::SERVICE */);
    }

    /**
     * {@inheritdoc}
     *
     * @return ArrayDefinition|ClosureDefinition|FactoryDefinition|IteratorDefinition|ObjectDefinition|UndefinedDefinition
     */
    public function singleton(string $abstract, $concrete = null)
    {
        $this->checkName($abstract);

        // If no concrete type was given, we will simply set the concrete type to the abstract type.
        return $this->definitions[$abstract] = self::createDefinition($abstract, $concrete ?? $abstract, 2 /* DefinitionContract::SINGLETON */);
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter(string $id, $value): DefinitionContract
    {
        $this->checkName($id, 'parameter');

        return $this->parameters[$id] = new ParameterDefinition($id, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function extend(string $abstract, Closure $closure): void
    {
        $this->extenders[$abstract][] = $closure;
    }

    /**
     * {@inheritdoc}
     */
    public function removeExtenders(string $abstract): void
    {
        unset($this->extenders[\array_key_exists($abstract, $this->aliases) ? $this->aliases[$abstract]->getName() : $abstract]);
    }

    /**
     * {@inheritdoc}
     */
    public function findDefinition(string $id): DefinitionContract
    {
        $seen = [];

        while (\array_key_exists($id, $this->aliases)) {
            $id = $this->aliases[$id]->getName();

            if (\array_key_exists($id, $seen)) {
                $seen = \array_values($seen);
                $seen = \array_slice($seen, \array_search($id, $seen, true));
                $seen[] = $id;

                throw new CircularDependencyException($id, $seen);
            }

            $seen[$id] = $id;
        }

        return $this->getDefinition($id);
    }

    /**
     * {@inheritdoc}
     *
     * @return ArrayDefinition|ClosureDefinition|FactoryDefinition|IteratorDefinition|ObjectDefinition|ParameterDefinition
     */
    public function getDefinition(string $id): DefinitionContract
    {
        if (! \array_key_exists($id, $this->definitions)) {
            throw new NotFoundException($id);
        }

        return $this->definitions[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter(string $id): DefinitionContract
    {
        if (! \array_key_exists($id, $this->parameters)) {
            throw new NotFoundException($id, null, null, [], \sprintf('You have requested a non-existent parameter [%s].', $id));
        }

        return $this->parameters[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $abstract): void
    {
        unset($this->definitions[$abstract], $this->aliases[$abstract]);
    }

    /**
     * {@inheritdoc}
     */
    public function removeParameter(string $id): void
    {
        unset($this->parameters[$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function removeDefinition(string $id): void
    {
        if (\array_key_exists($id, $this->definitions)) {
            unset($this->definitions[$id]);

            $this->removedIds[$id] = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTags(): array
    {
        $tags = [];

        foreach ($this->getDefinitions() as $definition) {
            foreach ($definition->getTags() as $tag => $value) {
                $tags[] = $tag;
            }
        }

        return \array_unique($tags);
    }

    /**
     * {@inheritdoc}
     */
    public function getTagged(string $tag): Traversable
    {
        $this->usedTags[] = $tag;

        $tags = 0;

        return new RewindableGenerator(function () use ($tag, &$tags) {
            foreach ($this->getDefinitions() as $id => $definition) {
                if ($definition instanceof TagAwareDefinitionContract && $definition->hasTag($tag)) {
                    $tags++;

                    yield $id => [$definition, $definition->getTag($tag)];
                }
            }
        }, $tags);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnusedTags(): array
    {
        return \array_values(\array_diff($this->getTags(), $this->usedTags));
    }

    /**
     * {@inheritdoc}
     */
    public function register(object $provider, array $parameters = []): void
    {
        $count = 0;
        $interfaces = \class_implements($provider, false);
        $serviceInterfaces = [
            AliasServiceProviderContract::class,
            ExtendServiceProviderContract::class,
            PipelineServiceProviderContract::class,
            ServiceProviderContract::class,
        ];

        foreach ($serviceInterfaces as $interface) {
            if (\array_key_exists($interface, $interfaces)) {
                $count++;
            }
        }

        if ($count === 0) {
            throw new InvalidArgumentException(\sprintf('Expected object that implements on of this interfaces [\'%s\']; received object with [\'%s\'] implemented interfaces.', \implode(', ', $serviceInterfaces), \implode(', ', $interfaces)));
        }

        foreach ($parameters as $key => $value) {
            $this->setParameter($key, $value);
        }

        if ($provider instanceof PipelineServiceProviderContract) {
            foreach ($provider->getPipelines() as $type => $pipelines) {
                foreach ($pipelines as $priority => $p) {
                    foreach ($p as $pipeline) {
                        $this->pipelineConfig->addPipe($pipeline, $type, $priority);
                    }
                }
            }
        }

        if ($provider instanceof ServiceProviderContract) {
            $provider->build($this);
        }

        if ($provider instanceof AliasServiceProviderContract) {
            foreach ($provider->getAlias() as $alias => $original) {
                if (\is_array($original)) {
                    if (isset($original[0]) && ! \is_string($original[0])) {
                        throw new InvalidArgumentException('The first entry of the alias array needs to be a [string].');
                    }

                    if (! isset($original[1])) {
                        throw new InvalidArgumentException('You forgot to provide the second entry on the alias array for the alias visibility; needs to be a [boolean].');
                    }

                    $this->setAlias($original[0], $alias)
                        ->setPublic($original[1]);
                } else {
                    $this->setAlias($original, $alias);
                }
            }
        }

        if ($provider instanceof ExtendServiceProviderContract) {
            foreach ($provider->getExtensions() as $key => $extension) {
                $this->extend($key, $extension);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setAlias(string $original, string $alias): AliasDefinitionContract
    {
        if (! \array_key_exists($original, $this->definitions) && ! \array_key_exists($original, $this->aliases)) {
            throw new LogicException(\sprintf('Alias [%s] cant be set to a undefined entry [%s].', $alias, $original));
        }

        if (\array_key_exists($alias, $this->aliases)) {
            $aliasName = \array_key_exists($original, $this->aliases) ? $this->aliases[$original]->getName() : null;

            if ($aliasName === $original) {
                throw new LogicException(\sprintf('[%s] is aliased to itself.', $original));
            }
        }

        if ($original === $alias) {
            throw new LogicException(\sprintf('Alias [%s] cant have the same name like a defined entry.', $alias));
        }

        unset($this->definitions[$alias], $this->removedIds[$alias]);

        return $this->aliases[$alias] = new AliasDefinition($original, $alias);
    }

    /**
     * {@inheritdoc}
     */
    public function removeAlias(string $alias): void
    {
        if (\array_key_exists($alias, $this->aliases)) {
            unset($this->aliases[$alias]);

            $this->removedIds[$alias] = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasAlias(string $id): bool
    {
        return \array_key_exists($id, $this->aliases);
    }

    /**
     * Compiles the container.
     *
     * @throws \LogicException
     *
     * @return void
     */
    public function compile(): void
    {
        $this->locked = true;

        try {
            foreach ($this->pipelineConfig->getPipelines() as $pipe) {
                $pipe->process($this);
            }
        } finally {
            $this->serviceReferenceGraph->reset();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isCompiled(): bool
    {
        return $this->locked;
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameter(string $id): bool
    {
        return \array_key_exists($id, $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function hasDefinition(string $id): bool
    {
        return \array_key_exists($id, $this->definitions);
    }

    /**
     * Just a alias.
     *
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return $this->hasDefinition(\array_key_exists($id, $this->aliases) ? $this->aliases[$id]->getName() : $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(string $id): AliasDefinitionContract
    {
        if (! \array_key_exists($id, $this->aliases)) {
            throw new NotFoundException($id, null, null, [], \sprintf('The service alias [%s] does not exist.', $id));
        }

        return $this->aliases[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function getClassReflector(string $class, bool $throw = true): ?ReflectionClass
    {
        return $this->protectedGetClassReflector($class, $throw);
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodReflector(ReflectionClass $classReflector, string $method): ReflectionFunctionAbstract
    {
        return $this->protectedGetMethodReflector($classReflector, $method);
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctionReflector($function): ReflectionFunction
    {
        return $this->protectedGetFunctionReflector($function);
    }

    /**
     * {@inheritdoc}
     */
    public function log(PipeContract $pass, string $message): void
    {
        if (\strpos($message, "\n") !== false) {
            $message = \str_replace("\n", "\n" . \get_class($pass) . ': ', \trim($message));
        }

        $this->logs[] = \get_class($pass) . ': ' . $message;
    }

    /**
     * Create a Definition on given value.
     *
     * @internal
     *
     * @param string $name
     * @param mixed  $value
     * @param int    $type
     * @param bool   $throw
     *
     * @throws \Viserio\Contract\Container\Exception\InvalidArgumentException
     *
     * @return ArrayDefinition|ClosureDefinition|FactoryDefinition|IteratorDefinition|ObjectDefinition|UndefinedDefinition
     */
    public static function createDefinition(string $name, $value, int $type, bool $throw = false): DefinitionContract
    {
        if ($value instanceof DefinitionContract || $value instanceof AliasDefinitionContract || $value instanceof ArgumentContract) {
            throw new InvalidArgumentException('A Definition or Argument class cant be used as value.');
        }

        if ($value instanceof Traversable) {
            return new IteratorDefinition($name, $value, $type);
        }

        if (! $value instanceof Closure && (\is_object($value) || is_class($value))) {
            return new ObjectDefinition($name, $value, $type);
        }

        if ($value instanceof Closure) {
            return new ClosureDefinition($name, $value, $type);
        }

        if (is_method($value) || \is_callable($value) || (\is_array($value) && isset($value[1]) && $value[1] === '__invoke') || (\is_array($value) && isset($value[0], $value[1]) && $value[0] instanceof ReferenceDefinitionContract && \is_string($value[1]))) {
            return new FactoryDefinition($name, $value, $type);
        }

        if (\is_array($value)) {
            return new ArrayDefinition($name, $value, $type);
        }

        if ($throw === false) {
            return new UndefinedDefinition($name, $value, $type);
        }

        throw new InvalidArgumentException(\sprintf('Registration failed for [%s], no suitable definition was found for type [%s]; If you tried to register a parameter, use the [setParameter] function.', $name, \is_object($value) ? \get_class($value) : \gettype($value)));
    }

    /**
     * Check if the given name is not empty.
     *
     * @param string $abstract
     * @param string $type
     *
     * @throws \Viserio\Contract\Container\Exception\InvalidArgumentException
     */
    private function checkName(string $abstract, string $type = 'service'): void
    {
        if ('' === $abstract || \strlen($abstract) !== \strcspn($abstract, "\0\r\n'")) {
            throw new InvalidArgumentException(\sprintf('Invalid %s id: "%s"', $type, $abstract));
        }
    }
}

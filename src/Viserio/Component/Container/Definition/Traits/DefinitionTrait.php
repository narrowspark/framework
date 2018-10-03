<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Definition\Traits;

use OutOfBoundsException;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\Container\Types as TypesContract;

trait DefinitionTrait
{
    /**
     * The binding name.
     *
     * @var string
     */
    protected $name;

    /**
     * The binding value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * All extender for the binding.
     *
     * @var array
     */
    protected $extenders = [];

    /**
     * Returns the list of parameter to pass when calling the class.
     *
     * @var null|array
     */
    protected $parameters = [];

    /**
     * Returns the list of tags.
     *
     * @var array
     */
    protected $tags = [];

    /**
     * Check if the binding is lazy.
     *
     * @var bool
     */
    protected $isLazy = false;

    /**
     * The binding type.
     *
     * @var int
     */
    protected $type;

    /**
     * Check if the binding is resolved.
     *
     * @var bool
     */
    protected $resolved = false;

    /**
     * The method name of the compile container extend function.
     *
     * @var string
     */
    protected $extendMethodName;

    /**
     * A ReflectionClass instance.
     *
     * @var \ReflectionFunction|\Roave\BetterReflection\Reflection\ReflectionClass|\Roave\BetterReflection\Reflection\ReflectionFunction|\Roave\BetterReflection\Reflection\ReflectionMethod
     */
    protected $reflector;

    /**
     * Extend this class to create new Definitions.
     *
     * @param string $name
     * @param int    $type
     */
    public function __construct(string $name, int $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
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
    public function isResolved(): bool
    {
        return $this->resolved;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtendMethodName(string $extendCompiledMethodName): void
    {
        $this->extendMethodName = $extendCompiledMethodName;
    }

    /**
     * {@inheritdoc}
     */
    public function getReflector()
    {
        return $this->reflector;
    }

    /**
     * {@inheritdoc}
     */
    public function replaceParameter($index, $parameter): void
    {
        if (\count($this->parameters) === 0) {
            throw new OutOfBoundsException('Cannot replace parameter if none have been configured yet.');
        }

        if (\is_int($index) && ($index < 0 || $index > \count($this->parameters) - 1)) {
            throw new OutOfBoundsException(\sprintf('The index "%d" is not in the range [0, %d].', $index, \count($this->parameters) - 1));
        }

        if (! \array_key_exists($index, $this->parameters)) {
            throw new OutOfBoundsException(\sprintf('The parameter "%s" doesn\'t exist.', $index));
        }

        $this->parameters[$index] = $parameter;
    }

    /**
     * {@inheritdoc}
     */
    public function isShared(): bool
    {
        return \in_array($this->type, [TypesContract::SINGLETON, TypesContract::PLAIN], true);
    }

    /**
     * {@inheritdoc}
     */
    public function setLazy(bool $bool): void
    {
        $this->isLazy = $bool;
    }

    /**
     * {@inheritdoc}
     */
    public function isLazy(): bool
    {
        return $this->isLazy;
    }

    /**
     * {@inheritdoc}
     */
    public function addExtender(\Closure $extender): void
    {
        $this->extenders[] = $extender;
    }

    /**
     * {@inheritdoc}
     */
    public function isExtended(): bool
    {
        return \count($this->extenders) !== 0;
    }

    /**
     * Extend a binding.
     *
     * @param mixed                             $binding
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return void
     */
    protected function extend(&$binding, ContainerInterface $container): void
    {
        foreach ($this->extenders as $extender) {
            $binding = $extender($container, $binding);
        }
    }
}

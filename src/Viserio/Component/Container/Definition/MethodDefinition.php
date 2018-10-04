<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Definition;

use OutOfBoundsException;
use Psr\Container\ContainerInterface;
use Viserio\Component\Container\Compiler\CompileHelper;
use Viserio\Component\Container\Definition\Traits\DefinitionTrait;
use Viserio\Component\Container\Definition\Traits\DeprecationTrait;
use Viserio\Component\Container\Definition\Traits\FactoryCompileParametersTrait;
use Viserio\Component\Container\Definition\Traits\ResolveParameterClassTrait;
use Viserio\Component\Container\Reflection\ReflectionFactory;
use Viserio\Component\Container\Reflection\ReflectionResolver;
use Viserio\Component\Contract\Container\Compiler\Definition as DefinitionContract;

/**
 * @internal
 */
final class MethodDefinition extends ReflectionResolver implements DefinitionContract
{
    use DefinitionTrait;
    use DeprecationTrait;
    use ResolveParameterClassTrait;
    use FactoryCompileParametersTrait;

    /**
     * Default deprecation template.
     *
     * @var string
     */
    protected $defaultDeprecationTemplate = 'The [%s] binding is deprecated. You should stop using it, as it will soon be removed.';

    /**
     * @var string
     */
    private $class;

    /**
     * @var array|\ReflectionParameter[]|\Roave\BetterReflection\Reflection\ReflectionParameter[]
     */
    private $classParameters;

    /**
     * Create a new Method Definition instance.
     *
     * @param string                $name
     * @param array|callable|string $value
     * @param int                   $type
     */
    public function __construct(string $name, $value, int $type)
    {
        $this->name = $name;
        $this->type = $type;

        $this->reflector       = ReflectionFactory::getMethodReflector($value);
        $this->parameters      = ReflectionFactory::getParameters($this->reflector);

        $implementingClass     = $this->reflector->getImplementingClass();
        $this->class           = $implementingClass->getName();
        $this->classParameters = ReflectionFactory::getParameters($implementingClass);
    }

    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassParameters(): array
    {
        return $this->classParameters;
    }

    public function replaceClass($class): void
    {
        $this->class = $class;
    }

    public function replaceClassParameter($index, $parameter): void
    {
        if (\count($this->classParameters) === 0) {
            throw new OutOfBoundsException('Cannot replace parameter if none have been configured yet.');
        }

        if (\is_int($index) && ($index < 0 || $index > \count($this->classParameters) - 1)) {
            throw new OutOfBoundsException(\sprintf('The index "%d" is not in the range [0, %d].', $index, \count($this->classParameters) - 1));
        }

        if (! \array_key_exists($index, $this->classParameters)) {
            throw new OutOfBoundsException(\sprintf('The parameter "%s" doesn\'t exist.', $index));
        }

        $this->classParameters[$index] = $parameter;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(ContainerInterface $container, array $parameters = []): void
    {
        $this->value = $this->resolveReflectionMethod($this->reflector, $this->parameters, $parameters);

        $this->extend($this->value, $container);

        $this->resolved = true;
    }

    /**
     * {@inheritdoc}
     */
    public function compile(): string
    {
        $compiledFactory = \sprintf('%s(%s)', $this->getCompiledFactory(), $this->compileParameters());

        if ($this->isExtended()) {
            return CompileHelper::compileExtend(
                $this->extenders,
                $compiledFactory,
                $this->extendMethodName,
                $this->isShared(),
                $this->getName()
            );
        }

        return CompileHelper::printReturn($compiledFactory, $this->isShared(), $this->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function getDebugInfo(): string
    {
        return 'Factory ' . $this->getCompiledFactory();
    }

    /**
     * Returns a compiled closure or a array printed to string.
     *
     * @return string
     */
    private function getCompiledFactory(): string
    {
        $functionName  = $this->reflector->getName();
//        $hasParameters = \count($this->classParameters) !== 0;

        $class              = $this->class;
        $compiledParameters = '';

        if (\mb_strpos($class, '$this') === false) {
            /** @var \ReflectionParameter|\Roave\BetterReflection\Reflection\ReflectionParameter $parameter */
            $parameters = \array_map(function ($parameter) {
                return CompileHelper::toVariableName($parameter->getName());
            }, $this->classParameters);

            $compiledParameters = \implode(', ', $parameters);
        }

        if ($functionName === '__invoke') {
            return \sprintf('(%s(%s))->__invoke', $class, $compiledParameters);
        }

        return \sprintf('[%s(%s), \'%s\']', $class, $compiledParameters, $functionName);
    }
}

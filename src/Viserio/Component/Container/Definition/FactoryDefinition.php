<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Definition;

use Psr\Container\ContainerInterface;
use Viserio\Component\Container\Compiler\CompileHelper;
use Viserio\Component\Container\Definition\Traits\DefinitionTrait;
use Viserio\Component\Container\Definition\Traits\DeprecationTrait;
use Viserio\Component\Container\Definition\Traits\ResolveParameterClassTrait;
use Viserio\Component\Container\Reflection\ReflectionFactory;
use Viserio\Component\Container\Reflection\ReflectionResolver;
use Viserio\Component\Contract\Container\Compiler\Definition as DefinitionContract;
use Viserio\Component\Contract\Container\Container as ContainerContract;

/**
 * @internal
 */
final class FactoryDefinition extends ReflectionResolver implements DefinitionContract
{
    use DefinitionTrait;
    use DeprecationTrait;
    use ResolveParameterClassTrait;

    /**
     * Default deprecation template.
     *
     * @var string
     */
    private $defaultDeprecationTemplate = 'The [%s] binding is deprecated. You should stop using it, as it will soon be removed.';

    /**
     * The reflection resolve method name.
     *
     * @var string
     */
    private $reflectionResolveName;

    /**
     * Create a new Factory Definition instance.
     *
     * @param string                $name
     * @param array|callable|string $value
     * @param int                   $type
     */
    public function __construct(string $name, $value, int $type)
    {
        $this->name = $name;
        $this->type = $type;

        if (is_function($value)) {
            $this->reflector             = ReflectionFactory::getFunctionReflector($value);
            $this->reflectionResolveName = 'Function';
        } else {
            $this->reflector             = ReflectionFactory::getMethodReflector($value);
            $this->reflectionResolveName = 'Method';
        }

        $this->parameters = ReflectionFactory::getParameters($this->reflector);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(ContainerInterface $container, array $parameters = []): void
    {
        $this->container = $container;

        if ($this->reflector->isClosure()) {
            \array_unshift($parameters, $container);
        }

        $this->value = $this->{'resolveReflection' . $this->reflectionResolveName}($this->reflector, $this->parameters, $parameters);

        $this->extend($this->value, $container);

        $this->resolved = true;
    }

    /**
     * {@inheritdoc}
     */
    public function compile(): string
    {
        $compiledFactory = \sprintf('%s' . $this->getCompiledFactory() . '%s', ($this->reflector->isClosure() ? '(' : ''), ($this->reflector->isClosure() ? ')' : ''));
        $compiledFactory .= '(' . $this->compileParameters() . ')';

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
        if ($this->reflector->isClosure()) {
            return CompileHelper::compileClosure($this->reflector->getClosure());
        }

        $functionName = $this->reflector->getName();

        if ($this->reflectionResolveName === 'Function') {
            return $functionName;
        }

        $reflectionClass = $this->reflector->getImplementingClass();
        $hasParameters   = \count(ReflectionFactory::getParameters($reflectionClass)) !== 0;

        if ($functionName === '__invoke') {
            return \sprintf('(new \%s())->__invoke', $reflectionClass->getName());
        }

        return \sprintf(
            '[%s' . $reflectionClass->getName() . '%s, \'' . $functionName . '\']',
            $hasParameters ? '\'' : 'new \\',
            $hasParameters ? '\'' : '()'
        );
    }

    /**
     * Compile factories parameters.
     *
     * @return string
     */
    private function compileParameters(): string
    {
        if (\count($this->parameters) === 0) {
            return '';
        }

        if (\count($this->parameters) === 1 && ! $this->parameters[0]->hasType()) {
            return '$this';
        }

        $preparedParameters = [];
        $isSkipped          = false;

        /** @var \ReflectionParameter|\Roave\BetterReflection\Reflection\ReflectionParameter $parameter */
        foreach ($this->parameters as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ContainerContract ||
                $type instanceof ContainerInterface ||
                (\count($this->parameters) >= 2 && ! $this->parameters[0]->hasType() && $isSkipped === false)
            ) {
                $isSkipped            = true;
                $preparedParameters[] = '$this';
            } else {
                $preparedParameters[] = CompileHelper::toVariableName($parameter->getName());
            }
        }

        return \implode(', ', $preparedParameters);
    }
}

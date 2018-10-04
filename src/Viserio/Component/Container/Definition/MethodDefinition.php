<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Definition;

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
     * @var \Roave\BetterReflection\Reflection\ReflectionClass
     */
    private $implementingClass;

    /**
     * @var array|\ReflectionParameter[]|\Roave\BetterReflection\Reflection\ReflectionParameter[]
     */
    private $implementingClassParameters;

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

        $this->reflector                   = ReflectionFactory::getMethodReflector($value);
        $this->parameters                  = ReflectionFactory::getParameters($this->reflector);
        $this->implementingClass           = $this->reflector->getImplementingClass();
        $this->implementingClassParameters = ReflectionFactory::getParameters($this->implementingClass);
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
        $hasParameters = \count($this->implementingClassParameters) !== 0;

        if ($functionName === '__invoke') {
            return \sprintf('(new \%s())->__invoke', $this->implementingClass->getName());
        }

        return \sprintf(
            '[%s%s%s, \'' . $functionName . '\']',
            $hasParameters ? '\'' : 'new \\',
            $this->implementingClass->getName(),
            $hasParameters ? '\'' : '()'
        );
    }
}

<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Definition;

use Psr\Container\ContainerInterface;
use stdClass;
use Viserio\Component\Container\Compiler\CompileHelper;
use Viserio\Component\Container\Definition\Traits\DefinitionTrait;
use Viserio\Component\Container\Definition\Traits\DeprecationTrait;
use Viserio\Component\Container\Definition\Traits\ResolveParameterClassTrait;
use Viserio\Component\Container\PhpParser\Helper;
use Viserio\Component\Container\Reflection\ReflectionFactory;
use Viserio\Component\Container\Reflection\ReflectionResolver;
use Viserio\Component\Contract\Container\Compiler\Definition as DefinitionContract;
use Viserio\Component\Contract\Container\LazyProxy\Instantiator as InstantiatorContract;

/**
 * @internal
 */
final class ObjectDefinition extends ReflectionResolver implements DefinitionContract
{
    use DefinitionTrait;
    use DeprecationTrait;
    use ResolveParameterClassTrait;

    /**
     * Default deprecation template.
     *
     * @var string
     */
    protected $defaultDeprecationTemplate = 'The [%s] binding is deprecated. You should stop using it, as it will soon be removed.';

    /**
     * @var null|\Viserio\Component\Contract\Container\LazyProxy\Instantiator
     */
    private $proxyInstantiator;

    /**
     * The original value.
     *
     * @var object|string
     */
    private $originalValue;

    /**
     * @var bool
     */
    private $inline;

    /**
     * Create a new Object Definition instance.
     *
     * @param string        $name
     * @param object|string $value
     * @param int           $type
     *
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     */
    public function __construct(string $name, $value, int $type)
    {
        $this->name = $name;
        $this->type = $type;

        $this->originalValue = $value;
        $this->reflector     = ReflectionFactory::getClassReflector($value);
        $this->parameters    = ReflectionFactory::getParameters($this->reflector);
    }

    public function inlineParameters(bool $bool): void {
        $this->inline = $bool;
    }

    /**
     * Sets the instantiator to be used when fetching proxies.
     *
     * @param \Viserio\Component\Contract\Container\LazyProxy\Instantiator $proxyInstantiator
     *
     * @return void
     */
    public function setInstantiator(InstantiatorContract $proxyInstantiator): void
    {
        $this->proxyInstantiator = $proxyInstantiator;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(ContainerInterface $container, array $parameters = []): void
    {
        $this->container = $container;

        if (! $this->reflector->isAnonymous()) {
            $this->value = $this->resolveReflectionClass($this->reflector, $this->parameters, $parameters);

            if (\is_object($this->originalValue)) {
                foreach ($this->reflector->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                    $name = $property->getName();

                    if ($this->originalValue instanceof stdClass) {
                        $value = $property->getDefaultValue();
                    } else {
                        $value = $property->getValue($this->originalValue);
                    }

                    if (! $property->isStatic()) {
                        $this->value->{$name} = $value;
                    } else {
                        $this->reflector->getName()::${$name} = $value;
                    }
                }
            }
        }

        if ($this->value === null) {
            $this->resolved = false;

            return;
        }

        if ($this->isLazy()) {
            $value = $this->value;
            $proxy = function () use ($value) {
                return $value;
            };

            $this->value = $this->proxyInstantiator->instantiateProxy($container, $this, $proxy);
        }

        if ($this->isExtended()) {
            $this->extend($this->value, $container);
        }

        $this->resolved = true;
    }

    /**
     * {@inheritdoc}
     */
    public function compile(): string
    {
        $compiledBinding = $this->compileObject();
        $isLazy          = $this->isLazy();
        $hasProperties   = false;

        if (! $this->reflector->isAnonymous()) {
            $properties = $this->reflector->getProperties(\ReflectionProperty::IS_PUBLIC);

            if (\count($properties) !== 0) {
                $hasProperties   = true;
                $compiledBinding = $this->compileProperties($compiledBinding, $properties);
            }
        }

        if ($isLazy) {
            $compiledBinding = CompileHelper::compileLazy($this->reflector->getName(), $compiledBinding, $this->parameters);
        }

        if ($this->isExtended()) {
            return CompileHelper::compileExtend(
                $this->extenders,
                $compiledBinding,
                $this->extendMethodName,
                $this->isShared(),
                $this->getName(),
                $hasProperties && $isLazy === false
            );
        }

        return CompileHelper::printReturn(
            $compiledBinding,
            $this->isShared(),
            $this->getName(),
            $hasProperties && $isLazy === false
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDebugInfo(): string
    {
        return ($this->isLazy() ? ' (Lazy) ' : '') . $this->compileObject();
    }

    /**
     * Compile object to string.
     *
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     *
     * @return string
     */
    private function compileObject(): string
    {
        /** @var \ReflectionParameter|\Roave\BetterReflection\Reflection\ReflectionParameter $parameter */
        $parameters = \array_map(function ($parameter) {
            /** @var null|\Roave\BetterReflection\Reflection\ReflectionClass $class*/
            $class = $parameter->getClass();

            if ($this->inline && $class !== null && $class->isInstantiable()) {
                return sprintf('new \\%s()', $class->getName());
            }

            return CompileHelper::toVariableName($parameter->getName());
        }, $this->parameters);

        $compiledParameters = \implode(', ', $parameters);

        if ($this->reflector->isAnonymous()) {
            $astClass = $this->reflector->getAst();

            return 'new ' . $this->doInsertStringBeforePosition(
                Helper::prettyAstPrint($astClass),
                    '(' . $compiledParameters . ')',
                5
            );
        }

        return \sprintf(
            'new %s(%s)',
            self::generateLiteralClass($this->reflector->getName()),
            $compiledParameters
        );
    }

    /**
     * Dumps a string to a literal (aka PHP Code) class value.
     *
     * @param string $class
     *
     * @return string
     */
    private static function generateLiteralClass(string $class): string
    {
        $class = \str_replace('\\\\', '\\', $class);

        return \mb_strpos($class, '\\') === 0 ? $class : '\\' . $class;
    }

    /**
     * Insert string before specified position.
     *
     * @param string $string
     * @param string $insertStr
     * @param int    $position
     *
     * @return string
     */
    private function doInsertStringBeforePosition(string $string, string $insertStr, int $position): string
    {
        return \mb_substr($string, 0, $position) . $insertStr . \mb_substr($string, $position);
    }

    /**
     * Compile default class properties.
     *
     * @param string $compiledBinding
     * @param array  $properties
     *
     * @return string
     */
    private function compileProperties(string $compiledBinding, array $properties): string
    {
        $compiledPropertyBinding = '$binding   = ' . \ltrim($compiledBinding) . ';' . \PHP_EOL;

        if (\is_object($this->originalValue)) {
            foreach ($properties as $property) {
                if ($this->originalValue instanceof stdClass) {
                    $value = $property->getDefaultValue();
                } else {
                    $value = $property->getValue($this->originalValue);
                }

                $buildProperty = $property->getName() . ' = ' . CompileHelper::compileValue($value);

                if (! $property->isStatic()) {
                    $compiledPropertyBinding .= '        $binding->' . $buildProperty . ';' . \PHP_EOL;
                } else {
                    $compiledPropertyBinding .= '        \\' . \ltrim($this->reflector->getName()) . '::$' . $buildProperty . ';' . \PHP_EOL;
                }
            }
        }

        return $compiledPropertyBinding;
    }
}

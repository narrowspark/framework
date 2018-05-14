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

namespace Viserio\Component\Container\Pipeline;

use ReflectionMethod;
use ReflectionParameter;
use Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;
use Twig\Extension\AbstractExtension;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Helper\Reflection;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Definition\FactoryDefinition as FactoryDefinitionContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Pipe as PipeContract;

/**
 * @inspiration https://github.com/nette/di/pull/178
 */
class AutowireArrayParameterCompilerPipe implements PipeContract
{
    /**
     * Default Classes that create circular dependencies.
     *
     * @var string[]
     */
    private static $defaultExcludedFatalClasses = [
        ContextProviderInterface::class,
        AbstractExtension::class,
    ];

    /**
     * Default Classes that create circular dependencies.
     *
     * @var string[]
     */
    private $excludedFatalClasses = [];

    /**
     * These namespaces are already configured by their bundles/extensions.
     *
     * @var string[]
     */
    private $excludedNamespaces = [
        'Doctrine',
        'JMS',
        'Symfony',
        'Twig',
    ];

    /**
     * @param string[] $excludedFatalClasses
     */
    public function __construct(array $excludedFatalClasses = [])
    {
        $this->excludedFatalClasses = \array_merge(self::$defaultExcludedFatalClasses, $excludedFatalClasses);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        /** @var \Viserio\Contract\Container\Definition\FactoryDefinition|\Viserio\Contract\Container\Definition\ObjectDefinition $definition */
        foreach ($containerBuilder->getDefinitions() as $definition) {
            if ($this->shouldSkipDefinition($containerBuilder, $definition)) {
                continue;
            }

            /** @var \ReflectionClass $reflectionClass */
            $reflectionClass = $containerBuilder->getClassReflector($definition->getClass());

            $this->processParameters($containerBuilder, $reflectionClass->getConstructor(), $definition);

            $def = $definition = null;
        }
    }

    /**
     * @param \Viserio\Contract\Container\ContainerBuilder      $containerBuilder
     * @param \Viserio\Contract\Container\Definition\Definition $definition
     *
     * @return bool
     */
    private function shouldSkipDefinition(
        ContainerBuilderContract $containerBuilder,
        DefinitionContract $definition
    ): bool {
        if (! $definition instanceof ObjectDefinitionContract && ! $definition instanceof FactoryDefinitionContract) {
            return true;
        }

        if (! $definition->isAutowired()) {
            return true;
        }

        $resolvedClassName = $definition->getClass();

        // skip 3rd party classes, they're autowired by own config
        if (\preg_match('#^(' . \implode('|', $this->excludedNamespaces) . ')\\\\#', $resolvedClassName)) {
            return true;
        }

        if (\in_array($resolvedClassName, $this->excludedFatalClasses, true)) {
            return true;
        }

        $reflectionClass = $containerBuilder->getClassReflector($definition->getClass());

        if ($reflectionClass === null) {
            return true;
        }

        if (! $reflectionClass->hasMethod('__construct')) {
            return true;
        }

        /** @var \ReflectionMethod $constructorMethodReflection */
        $constructorMethodReflection = $reflectionClass->getConstructor();

        if (\count($constructorMethodReflection->getParameters()) === 0) {
            return true;
        }

        return false;
    }

    /**
     * @param \Viserio\Contract\Container\ContainerBuilder                                                                     $containerBuilder
     * @param \ReflectionMethod                                                                                                $reflectionMethod
     * @param \Viserio\Contract\Container\Definition\FactoryDefinition|\Viserio\Contract\Container\Definition\ObjectDefinition $definition
     *
     * @return void
     */
    private function processParameters(
        ContainerBuilderContract $containerBuilder,
        ReflectionMethod $reflectionMethod,
        $definition
    ): void {
        foreach ($reflectionMethod->getParameters() as $parameterReflection) {
            if ($this->shouldSkipParameter($reflectionMethod, $definition, $parameterReflection)) {
                continue;
            }

            $parameterType = $this->resolveParameterType($parameterReflection->getName(), $reflectionMethod);
            $definitionsOfType = $this->findAllByType($containerBuilder, $parameterType);

            $definition->setArgument('$' . $parameterReflection->getName(), $this->createReferencesFromDefinitions($definitionsOfType));
        }
    }

    /**
     * @param \ReflectionMethod                                                                                                $reflectionMethod
     * @param \Viserio\Contract\Container\Definition\FactoryDefinition|\Viserio\Contract\Container\Definition\ObjectDefinition $definition
     * @param \ReflectionParameter                                                                                             $reflectionParameter
     *
     * @return bool
     */
    private function shouldSkipParameter(
        ReflectionMethod $reflectionMethod,
        $definition,
        ReflectionParameter $reflectionParameter
    ): bool {
        if (! $reflectionParameter->isArray()) {
            return true;
        }
        // already set
        $argumentName = '$' . $reflectionParameter->getName();

        if (isset($definition->getArguments()[$argumentName])) {
            return true;
        }
        $parameterType = $this->resolveParameterType($reflectionParameter->getName(), $reflectionMethod);

        if ($parameterType === null) {
            return true;
        }

        if (\in_array($parameterType, $this->excludedFatalClasses, true)) {
            return true;
        }

        if (! \class_exists($parameterType) && ! \interface_exists($parameterType)) {
            return true;
        }

        // prevent circular dependency
        if ($definition->getClass() === null) {
            return false;
        }

        if (\is_a($definition->getClass(), $parameterType, true)) {
            return true;
        }

        return false;
    }

    /**
     * @param string            $parameterName
     * @param \ReflectionMethod $reflectionMethod
     *
     * @return null|string
     */
    private function resolveParameterType(string $parameterName, ReflectionMethod $reflectionMethod): ?string
    {
        $parameterDocTypeRegex = '#@param[ \t]+(?<type>[\w\\\\]+)\[\][ \t]+\$' . $parameterName . '#';
        // copied from https://github.com/nette/di/blob/d1c0598fdecef6d3b01e2ace5f2c30214b3108e6/src/DI/Autowiring.php#L215
        \preg_match($parameterDocTypeRegex, (string) $reflectionMethod->getDocComment(), $result);

        if ($result === null || \count($result) === 0) {
            return null;
        }

        // not a class|interface type
        if (\ctype_lower($result['type'][0])) {
            return null;
        }

        return Reflection::expandClassName($result['type'], $reflectionMethod->getDeclaringClass());
    }

    /**
     * @param \Viserio\Contract\Container\Definition\Definition[] $definitions
     *
     * @return \Viserio\Contract\Container\Definition\ReferenceDefinition[]
     */
    private function createReferencesFromDefinitions(array $definitions): array
    {
        $references = [];

        foreach (\array_keys($definitions) as $definitionOfTypeName) {
            $references[] = new ReferenceDefinition($definitionOfTypeName);
        }

        return $references;
    }

    /**
     * @param \Viserio\Contract\Container\ContainerBuilder $containerBuilder
     * @param string                                       $type
     *
     * @return \Viserio\Contract\Container\Definition\Definition[]
     */
    private function findAllByType(ContainerBuilderContract $containerBuilder, string $type): array
    {
        $definitions = [];

        foreach ($containerBuilder->getDefinitions() as $definition) {
            if ($definition instanceof ObjectDefinitionContract || $definition instanceof FactoryDefinitionContract) {
                $class = $definition->getClass();
                $name = $definition->getName();

                if (\is_a($class, $type, true)) {
                    $definitions[$name] = $definition;
                }
            }
        }

        return $definitions;
    }
}

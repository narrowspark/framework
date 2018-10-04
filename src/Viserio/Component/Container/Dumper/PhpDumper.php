<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Dumper;

use LogicException;
use Psr\Container\ContainerInterface;
use RuntimeException as BaseRuntimeException;
use Symfony\Component\VarExporter\VarExporter;
use Viserio\Component\Container\Compiler\CompileHelper;
use Viserio\Component\Container\Container;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\FactoryDefinition;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Contract\Container\Compiler\Definition as DefinitionContract;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Container\Exception\RuntimeException;

/**
 * @internal
 */
final class PhpDumper
{
    /**
     * Name of the container class, used to create the container.
     *
     * @var string
     */
    private $containerClass;

    /**
     * Name of the container parent class, used on compiled container.
     *
     * @var string
     */
    private $containerParentClass;

    /**
     * Namespace of the container class, used on compiled container.
     *
     * @var string
     */
    private $containerNamespace;

    /**
     * The method name of the compile container extend function.
     *
     * @var string
     */
    private $extendCompiledMethodName;

    /**
     * Create a new PhpDumper instance.
     *
     * @param string      $class
     * @param string      $parentClass
     * @param null|string $namespace
     */
    public function __construct(string $class, string $parentClass, ?string $namespace = null)
    {
        $this->extendCompiledMethodName = $this->generateUniqueName('extend');

        $this->containerClass       = \ltrim($class, '\\');
        $this->containerParentClass = \ltrim($parentClass, '\\');
        $this->containerNamespace   = $namespace;
    }

    /**
     * Compile the container.
     *
     * @param string                                        $cacheDirectory
     * @param \Viserio\Component\Container\ContainerBuilder $container
     *
     * @return string The compiled container file name
     */
    public function compile(string $cacheDirectory, ContainerBuilder $container): string
    {
        $fileName = self::getFileName($cacheDirectory, $this->containerClass);

        // The container is already compiled
        if (\file_exists($fileName)) {
            return $fileName;
        }

        // Validate that a valid class name was provided
        if (! \preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $this->containerClass)) {
            throw new InvalidArgumentException(\sprintf('The container cannot be compiled: [%s] is not a valid PHP class name.', $this->containerClass));
        }

        $methods                      = [];
        $methodMap                    = [];
        $parentClass                  = $this->generateUniqueName('Container');
        $alreadyGeneratedProxyClasses = [];

        $definitions = $container->getDefinitions();
        \ksort($definitions);

        foreach ($definitions as $id => $definition) {
            $methodMap[$id] = $this->generateUniqueName('get', $id);
        }

        foreach ($definitions as $id => $definition) {
            $definition->setExtendMethodName($this->extendCompiledMethodName);

            if ($definition instanceof ObjectDefinition && $definition->isLazy()) {
                $className = $definition->getReflector()->getName();

                if (! isset($alreadyGeneratedProxyClasses[$className])) {
                    $proxyClassCode = CompileHelper::compileLazyClass($className);
                    $proxyFileName  = \explode(' ', $proxyClassCode, 3)[1];

                    \file_put_contents(
                        self::getFileName($cacheDirectory, $proxyFileName),
                        '<?php' . \PHP_EOL . 'declare(strict_types=1);' . \PHP_EOL . \PHP_EOL . $proxyClassCode
                    );

                    $alreadyGeneratedProxyClasses[$className] = true;
                }
            }

            $methodParameters = $this->resolveDefinitionParameters($definition, $definition->getParameters(), $methodMap);

            foreach ($container->getExtenders($id) as $extender) {
                $definition->addExtender($extender);
            }

            $code = $definition->compile();

            if ($definition->isDeprecated()) {
                $code = \sprintf('        @\trigger_error(%s, \E_USER_DEPRECATED);' . \PHP_EOL . \PHP_EOL, $definition->getDeprecationMessage($id)) . $code;
            }

            $methods[] = $this->generateMethod($methodMap[$id], $code, $methodParameters) . \PHP_EOL;
        }

        $fileContent = $this->getClassStartForCompiledClass($parentClass, $this->containerParentClass . ' as ' . $parentClass);
        $fileContent .= '    protected static $methodMapping = ' . VarExporter::export($methodMap) . ';' . \PHP_EOL . \PHP_EOL;
        $fileContent .= '    /**' . \PHP_EOL . '     * {@inheritdoc}' . \PHP_EOL . '     */' . \PHP_EOL . '    protected $tags = ' . VarExporter::export($container->getTags()) . ';' . \PHP_EOL . \PHP_EOL;
        $fileContent .= $this->getClassAliases($container);
        $fileContent .= $this->getExtendMethodForCompiledClass();
        $fileContent .= \implode('', $methods);
        $fileContent .= '    protected function loadFile(string $file): bool' . \PHP_EOL . '    {' . \PHP_EOL . '        return (bool) require_once __DIR__ . DIRECTORY_SEPARATOR . $file;' . \PHP_EOL . '    }' . \PHP_EOL . \PHP_EOL;
        $fileContent .= '    protected function createProxy(string $class, \Closure $factory)' . \PHP_EOL . '    {' . \PHP_EOL . '        \class_exists($class, false) || $this->loadFile($class . \'.php\');' . \PHP_EOL . \PHP_EOL . '        return $factory();' . \PHP_EOL . '    }' . \PHP_EOL;
        $fileContent .= $this->getClassEndForCompiledClass();

        self::createCompilationDirectory(\dirname($fileName));

        $successful = \file_put_contents($fileName, $fileContent);

        if ($successful === false) {
            throw new RuntimeException(\sprintf('Failed to write file [%s].', $fileName));
        }

        return $fileName;
    }

    /**
     * Check if a parameter is container instance/class.
     *
     * @param mixed $parameter
     *
     * @return bool
     */
    public function hasContainerParameter($parameter): bool
    {
        return \in_array($parameter, [ContainerContract::class, ContainerBuilder::class, ContainerInterface::class, Container::class], true);
    }

    /**
     * @param array                                                     $definitionArguments
     * @param \Viserio\Component\Contract\Container\Compiler\Definition $definition
     * @param array                                                     $methodMap
     *
     * @return mixed
     */
    private function resolveDefinitionParameters(
        DefinitionContract $definition,
        array $definitionArguments,
        array $methodMap
    ) {
        $containerParameters = [];

        foreach ($definitionArguments as $definitionId => $parameters) {
            if ($definitionId === $definition->getName()) {
                /** @var \ReflectionParameter|\Roave\BetterReflection\Reflection\ReflectionParameter $parameter */
                foreach ($parameters as $key => $parameter) {
                    if ($definition instanceof FactoryDefinition && ($key === 0 || $this->hasContainerParameter($parameter->getName()))) {
                        $containerParameters[] = $key;

                        $definition->replaceParameter($key, '$this');
                    } else {
                        $definition->replaceParameter($key, $this->resolveParameter($parameter, $methodMap));
                    }
                }
            }
        }

        $methodParameters = $definition->getParameters();

        foreach ($containerParameters as $key) {
            unset($methodParameters[$key]);
        }

        return $methodParameters;
    }

    /**
     * @param \ReflectionParameter|\Roave\BetterReflection\Reflection\ReflectionParameter $parameter
     * @param array                                                                       $methodMap
     *
     * @return mixed
     */
    private function resolveParameter($parameter, array $methodMap)
    {
        if ($parameter->hasType() && ! $parameter->getType()->isBuiltin()) {
            try {
                $class = $parameter->getClass();
            } catch (BaseRuntimeException $exception) {
                $class = null;
            }

            if ($class === null && $parameter->allowsNull()) {
                return 'null';
            }

            return $methodMap[$class->getName()] ?? $class->getName();
        }

        // !optional + defaultAvailable = func($a = null, $b) since 5.4.7
        // optional + !defaultAvailable = i.e. Exception::__construct, mysqli::mysqli, ...
        if ($parameter->isOptional() || $parameter->isDefaultValueAvailable() || ($parameter->hasType() && $parameter->allowsNull())) {
            return $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : 'null';
        }

        return CompileHelper::toVariableName($parameter->getName());
    }

    /**
     * @param string $directory
     *
     * @return void
     */
    private static function createCompilationDirectory(string $directory): void
    {
        if (! \is_dir($directory) && ! @\mkdir($directory, 0777, true)) {
            throw new InvalidArgumentException(\sprintf('Compilation directory does not exist and cannot be created: %s.', $directory));
        }

        if (! \is_writable($directory)) {
            throw new InvalidArgumentException(\sprintf('Compilation directory is not writable: %s.', $directory));
        }
    }

    /**
     * Use a hash to ensure that the used method names are both unique and idempotent.
     *
     * @param string $prefix
     * @param string $id
     *
     * @return string
     */
    private function generateUniqueName(string $prefix, string $id = ''): string
    {
        return $prefix . CompileHelper::getHashedValue($id);
    }

    /**
     * Generate a container method.
     *
     * @param string $uniqueMethodName
     * @param string $content
     * @param array  $parameters
     * @param bool   $static
     *
     * @return string
     */
    private function generateMethod(
        string $uniqueMethodName,
        string $content,
        array $parameters = [],
        bool $static      = false
    ): string {
        if (\count($parameters) === 1 && ! $parameters[0]->hasType()) {
            $stringParameters = '';
        } else {
            $preparedParameters = [];
            $isSkipped          = false;

            /** @var \ReflectionParameter|\Roave\BetterReflection\Reflection\ReflectionParameter $parameter */
            foreach ($parameters as $parameter) {
                $type = $parameter->getType();

                if ((\count($parameters) >= 2 && ! $parameters[0]->hasType() && $isSkipped === false) ||
                    $type instanceof ContainerContract ||
                    $type instanceof ContainerInterface
                ) {
                    $isSkipped = true;

                    continue;
                }

                try {
                    $defaultValue = ' = ' . VarExporter::export($parameter->getDefaultValue());
                } catch (LogicException $e) {
                    $defaultValue = '';
                }

                $preparedParameters[] = ($parameter->hasType() ? $type . ' ' : '') . CompileHelper::toVariableName($parameter->getName()) . $defaultValue;
            }

            $stringParameters = \implode(', ', $preparedParameters);
        }

        return '    protected' . ($static ? ' static' : '') . ' function ' . $uniqueMethodName . '(' . $stringParameters . ')' . \PHP_EOL . '    {' . \PHP_EOL . $content . \PHP_EOL . '    }' . \PHP_EOL;
    }

    /**
     * Returns the start of the class.
     *
     * @param string $parentClass
     * @param string $use
     *
     * @return string
     */
    private function getClassStartForCompiledClass(string $parentClass, string $use): string
    {
        $fileContent = '<?php' . \PHP_EOL . 'declare(strict_types=1);' . \PHP_EOL;
        $fileContent .= ($this->containerNamespace !== null ? 'namespace ' . $this->containerNamespace . ';' : '') . \PHP_EOL . \PHP_EOL;
        $fileContent .= 'use ' . $use . ';' . \PHP_EOL . \PHP_EOL;
        $fileContent .= '/**' . \PHP_EOL . ' * This class has been auto-generated by Viserio Container Component.' . \PHP_EOL . ' */' . \PHP_EOL;

        return $fileContent . 'final class ' . $this->containerClass . ' extends ' . $parentClass . \PHP_EOL . '{' . \PHP_EOL;
    }

    /**
     * Returns the extend method.
     *
     * @return string
     */
    private function getExtendMethodForCompiledClass(): string
    {
        return '    protected function ' . $this->extendCompiledMethodName . '(array $extenders, &$binding): void ' . \PHP_EOL . '    {' . \PHP_EOL .
            '        foreach ($extenders as $extender) {' . \PHP_EOL .
            '            $binding = $extender($this, $binding);' . \PHP_EOL .
            '        }' . \PHP_EOL .
            '    }' . \PHP_EOL . \PHP_EOL;
    }

    /**
     * Return the end of the class.
     *
     * @return string
     */
    private function getClassEndForCompiledClass(): string
    {
        return '}';
    }

    /**
     * Retrieves the file name for the given class name.
     *
     * @param string $directory
     * @param string $className
     *
     * @return string
     */
    private static function getFileName(string $directory, string $className): string
    {
        return \rtrim($directory, '/') . \DIRECTORY_SEPARATOR . \str_replace('\\', '', $className) . '.php';
    }

    /**
     * Create the alias and deprecatedAliases property for the container.
     *
     * @param \Viserio\Component\Container\ContainerBuilder $container
     *
     * @return string
     */
    private function getClassAliases(ContainerBuilder $container): string
    {
        $aliases           = [];
        $deprecatedAliases = [];

        foreach ($container->getAliases() as $alias) {
            $aliases[$alias->getAlias()] = $alias->getName();
        }

        $inheritdoc = '    /**' . \PHP_EOL . '     * {@inheritdoc}' . \PHP_EOL . '     */' . \PHP_EOL;
        $content    = $inheritdoc . '    protected $aliases = ' . VarExporter::export($aliases) . ';' . \PHP_EOL . \PHP_EOL;

        return $content . $inheritdoc . '    protected $deprecatedAliases = ' . VarExporter::export($deprecatedAliases) . ';' . \PHP_EOL . \PHP_EOL;
    }
}

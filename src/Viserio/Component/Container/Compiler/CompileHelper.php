<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Compiler;

use Closure;
use Opis\Closure\ReflectionClosure;
use Opis\Closure\SerializableClosure;
use PhpParser\Node\Expr\New_;
use ProxyManager\GeneratorStrategy\BaseGeneratorStrategy;
use ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionFunction;
use Symfony\Component\VarExporter\VarExporter;
use Viserio\Component\Container\LazyProxy\LazyLoadingValueHolderGenerator;
use Viserio\Component\Container\PhpParser\Helper;
use Viserio\Component\Contract\Container\Compiler\Definition as DefinitionContract;
use Zend\Code\Generator\ClassGenerator;

/**
 * @internal
 */
final class CompileHelper
{
    /**
     * A salt string.
     *
     * @var string
     */
    public const SALT = "pW;RK!~D)@C*?FVg[O6-#rsld_tF=c`!8A&x7:Q?c3='<O#jp\$U Vohg0,BO Xzv";

    /**
     * Uses cache based on files.
     *
     * @var array
     */
    private static $usesCache = [];

    /**
     * A LazyLoadingValueHolderGenerator instance.
     *
     * @var \Viserio\Component\Container\LazyProxy\LazyLoadingValueHolderGenerator
     */
    private static $proxyGenerator;

    /**
     * A BaseGeneratorStrategy instance.
     *
     * @var \ProxyManager\GeneratorStrategy\BaseGeneratorStrategy
     */
    private static $classGenerator;

    /**
     * Private constructor; non-instantiable.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Get a hash from the given value.
     *
     * @param string $value
     *
     * @return string
     */
    public static function getHashedValue(string $value): string
    {
        return \md5($value . self::SALT);
    }

    /**
     * Analyze a closure.
     *
     * @param \Closure $closure
     *
     * @return string
     */
    public static function compileClosure(Closure $closure): string
    {
        $closureInfo = new ReflectionClosure($closure);

        if (\count($closureInfo->getUseVariables()) !== 0) {
            return '\unserialize(\'' . \serialize(new SerializableClosure($closure)) . '\')';
        }

        $closureInfo = ReflectionFunction::createFromClosure($closure);
        $filePath    = $closureInfo->getFileName();

        if (! isset(self::$usesCache[$filePath])) {
            self::$usesCache[$filePath] = Helper::getUsesFromSource($closureInfo->getLocatedSource()->getSource());
        }

        $ast = $closureInfo->getAst();

        foreach ($ast->stmts[0] as $statement) {
            if ($statement instanceof New_) {
                $name = $statement->class;

                if ($name === null) {
                    continue;
                }

                $className = \ltrim(\implode('\\', $name->parts), '\\');

                foreach (self::$usesCache[$filePath] as $use => $alias) {
                    if (\mb_strpos($use, $className) !== false || ($alias !== null && \mb_strpos($alias, $className) !== false)) {
                        $name->parts = ['\\' . $use];
                    }
                }
            }
        }

        $ast->static = true;

        return \trim(Helper::prettyAstPrint($ast), "\t\n\r;");
    }

    /**
     * Compile know values to executable php code for the compiled container.
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function compileValue($value): string
    {
        if ($value instanceof DefinitionContract) {
            $value = $value->compile();
        }

        if ($value instanceof Closure) {
            return self::compileClosure($value);
        }

        if (\is_array($value)) {
            foreach ($value as $key => $v) {
                if ($v instanceof Closure) {
                    $value[$key] = self::compileClosure($v);
                } elseif ($v instanceof DefinitionContract) {
                    $value[$key] = $v->compile();
                }
            }
        }

        return VarExporter::export($value);
    }

    /**
     * Returns the next available variable name to use.
     *
     * @param string $variable      The variable name we wish to use
     * @param array  $usedVariables the list of variable names already used
     *
     * @return string
     */
    public static function getNextAvailableVariableName(string $variable, array $usedVariables): string
    {
        $variable = self::toVariableName($variable);

        while (true) {
            // check that the name is not reserved
            if (! \in_array($variable, $usedVariables, true)) {
                break;
            }

            $numbers = '';

            while (true) {
                $lastCharacter = \mb_substr($variable, \mb_strlen($variable) - 1);

                if ($lastCharacter >= '0' && $lastCharacter <= '9') {
                    $numbers  = $lastCharacter . $numbers;
                    $variable = \mb_substr($variable, 0, \mb_strlen($variable) - 1);
                } else {
                    break;
                }
            }

            if ($numbers === '') {
                $numbers = 0;
            } else {
                $numbers = (int) $numbers;
            }

            $numbers++;

            $variable .= $numbers;
        }

        return $variable;
    }

    /**
     * Transform $name into a valid variable name by removing not authorized characters or adding new ones.
     *
     * - foo => $foo
     * - $foo => $foo
     * - fo$o => $foo
     *
     * @param string $name
     *
     * @return string
     */
    public static function toVariableName(string $name): string
    {
        $variableName = \preg_replace('/[^A-Za-z0-9]/', '', $name);

        if ($variableName[0] >= '0' && $variableName[0] <= '9') {
            $variableName = 'a' . $variableName;
        }

        return '$' . $variableName;
    }

    /**
     * A compiled string with the proxy wrapper.
     *
     * @param string $class
     * @param string $compiledBinding
     * @param array  $parameters
     *
     * @return string
     */
    public static function compileLazy(string $class, string $compiledBinding, array $parameters = []): string
    {
        $className = self::getProxyClassName($class);

        if (\count($parameters) === 0) {
            $uses = '';
            $wrappedInstance = '$wrappedInstance = ' . $compiledBinding . ';';
        } else {
            $uses            = 'use (' . \implode(', ', $parameters). ')';
            $wrappedInstance = $compiledBinding . \PHP_EOL;
            $wrappedInstance .= '                $wrappedInstance = $binding;';
        }

        return '$this->createProxy(\'' . $className . '\', static function() '.$uses.' {
            $proxy = static function (&$wrappedInstance, \ProxyManager\Proxy\LazyLoadingInterface $proxy) {
                ' . $wrappedInstance . '
                $proxy->setProxyInitializer(null);
                return true;
            };

            return \\' . $className . '::staticProxyConstructor($proxy);
        })';
    }

    /**
     * A compiled string with the proxy class.
     *
     * @param string $className
     *
     * @throws \ReflectionException
     *
     * @return string
     */
    public static function compileLazyClass(string $className): string
    {
        $classGenerator = self::generateProxyClass(new ReflectionClass($className));
        $generatedCode  = self::getClassGenerator()->generate($classGenerator);

        return \preg_replace(
            '/((?:\$(?:this|initializer|instance)->)?(?:publicProperties|initializer|valueHolder))[0-9a-f]++/',
            '${1}' . self::getHashedValue($className),
            $generatedCode
        );
    }

    /**
     * Prints the return on if the binding is shared.
     *
     * @param string      $return
     * @param bool        $shared
     * @param null|string $bindingName
     * @param bool        $hasProperties
     *
     * @return string
     */
    public static function printReturn(
        string $return,
        bool $shared        = false,
        string $bindingName = null,
        bool $hasProperties = false
    ): string {
        if ($shared && $bindingName) {
            if ($hasProperties === false) {
                return '        return $this->services[\'' . $bindingName . '\'] = ' . $return . ';';
            }

            return '        ' . $return . \PHP_EOL . '        return $this->services[\'' . $bindingName . '\'] = $binding;';
        }

        if ($hasProperties === false) {
            return '        return ' . $return . ';';
        }

        return '        ' . $return . \PHP_EOL . '        return $binding;';
    }

    /**
     * Warps the compiled string in a extended one.
     *
     * @param array       $extenders
     * @param string      $compiledBinding
     * @param string      $extendMethodName
     * @param bool        $shared
     * @param null|string $bindingName
     * @param bool        $hasProperties
     *
     * @return string
     */
    public static function compileExtend(
        array $extenders,
        string $compiledBinding,
        string $extendMethodName,
        bool $shared        = false,
        string $bindingName = null,
        bool $hasProperties = false
    ): string {
        $extenders = \array_map(function (Closure $extender) {
            return self::compileClosure($extender);
        }, $extenders);

        if ($hasProperties === false) {
            $code = '        $binding   = ' . \ltrim($compiledBinding) . ';' . \PHP_EOL;
        } else {
            $code = $compiledBinding . \PHP_EOL;
        }

        $code .= '        $extenders = [' . \PHP_EOL . '        ' . \implode(',' . \PHP_EOL . '        ', $extenders) . \PHP_EOL . '        ];' . \PHP_EOL;
        $code .= \PHP_EOL . '        $this->' . $extendMethodName . '($extenders, $binding);' . \PHP_EOL . \PHP_EOL;

        return $code . self::printReturn('$binding', $shared, $bindingName);
    }

    /**
     * Get the LazyLoadingValueHolderGenerator.
     *
     * @return \Viserio\Component\Container\LazyProxy\LazyLoadingValueHolderGenerator
     */
    private static function getProxyGenerator(): LazyLoadingValueHolderGenerator
    {
        if (self::$proxyGenerator === null) {
            self::$proxyGenerator = new LazyLoadingValueHolderGenerator();
        }

        return self::$proxyGenerator;
    }

    /**
     * Get the LazyLoadingValueHolderGenerator.
     *
     * @return \ProxyManager\GeneratorStrategy\BaseGeneratorStrategy
     */
    private static function getClassGenerator(): BaseGeneratorStrategy
    {
        if (self::$classGenerator === null) {
            self::$classGenerator = new BaseGeneratorStrategy();
        }

        return self::$classGenerator;
    }

    /**
     * Produces the proxy class name for the given definition.
     *
     * @param string $className
     *
     * @return string
     */
    private static function getProxyClassName(string $className): string
    {
        return \preg_replace('/^.*\\\\/', '', $className) . '_' . self::getHashedValue($className);
    }

    /**
     * @param \ReflectionClass $reflectionClass
     *
     * @return \Zend\Code\Generator\ClassGenerator
     */
    private static function generateProxyClass(ReflectionClass $reflectionClass): ClassGenerator
    {
        $generatedClass = new ClassGenerator(self::getProxyClassName($reflectionClass->getName()));

        self::getProxyGenerator()->generate($reflectionClass, $generatedClass);

        return $generatedClass;
    }
}

<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Compiler;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use Viserio\Component\Container\Util\Reflection;
use Viserio\Component\Contract\Container\Exception\CompileException;
use Viserio\Component\Contract\Container\Types as TypesContract;

final class ObjectCompiler extends AbstractCompiler
{
    /**
     * {@inheritdoc}
     */
    public function isSupported(string $id, array $binding): bool
    {
        $value = $binding[TypesContract::VALUE];

        return $binding[TypesContract::BINDING_TYPE] !== TypesContract::LAZY &&
            (
                (\is_string($value) && \class_exists($value)) ||
                (\is_object($value) && ! $value instanceof Closure)
            );
    }

    /**
     * {@inheritdoc}
     */
    public function compile(string $id, array $binding): string
    {
        $value = $binding[TypesContract::VALUE];

        if (\method_exists($value, '__invoke') && \count(\get_class_methods($value)) === 1) {
            return $this->compileInvokeClass($binding);
        }

        $reflection = new ReflectionClass($value);

        return $this->compileObject($reflection);
    }

    /**
     * @param array $binding
     *
     * @return string
     */
    private function compileInvokeClass(array $binding): string
    {
        $value = $binding[TypesContract::VALUE];

        $className = \var_export($value, true);

        $code  = '        if (! isset($this->resolvedEntries[' . $className . '])) {' . PHP_EOL;
        $code .= '            $this->resolvedEntries[' . $className . '] = $this->resolveNonBound(' . $className . ');' . PHP_EOL;
        $code .= '        }' . PHP_EOL . PHP_EOL;

        return $code . '        return $this->getFactoryInvoker()->call(' . $className . ');';
    }

    /**
     * @param \ReflectionClass $reflection
     *
     * @throws \ReflectionException
     * @throws \Viserio\Component\Contract\Container\Exception\CompileException
     *
     * @return string
     */
    private function compileObject(ReflectionClass $reflection): string
    {
        if (\mb_strpos($reflection->getName(), '@') !== false) {
            throw new CompileException('Anonymous classes cannot be compiled.');
        }

        if (! $reflection->isInstantiable()) {
            if (! \class_exists($reflection->getName())) {
                throw new CompileException(\sprintf('Unable to reflect on the class [%s], does the class exist and is it properly autoloaded?', $reflection->getName()));
            }

            throw new CompileException(\sprintf('The class [%s] is not instantiable.', $reflection->getName()));
        }

        $parameters = [];
        $method     = $reflection->getConstructor();
        $code       = '';

        if ($method !== null) {
            foreach ($method->getParameters() as $parameter) {
                $parameters[$parameter->getName()] = $this->resolveParameter($parameter);
            }

            foreach ($parameters as $key => $parameter) {
                if (\is_string($parameter) && \class_exists($parameter)) {
                    $class = $parameter;

                    $object = $this->compileObject(new ReflectionClass($class));
                    $code .= \str_replace('$object', '$' . $key, $object);

                    $parameters[$key] = '$' . $key;
                }
            }

            $parameters = \array_map(function ($value) {
                return $this->compileValue($value);
            }, $parameters);
        }

        $className = $this->generateLiteralClass($reflection->getName());

        if (\count($parameters) !== 0) {
            $code .= \sprintf(
                    '        $object = new %s(%s);',
                    $className,
                    \implode(' ,', $parameters)
                ) . PHP_EOL . PHP_EOL;

            return $code . '        return $object;';
        }

        return \sprintf('        return new %s();', $className);
    }

    /**
     * Resolve a parameter.
     *
     * @param \ReflectionParameter $parameter
     *
     * @throws \ReflectionException
     * @throws \Viserio\Component\Contract\Container\Exception\CompileException
     *
     * @return mixed
     */
    private function resolveParameter(ReflectionParameter $parameter)
    {
        $type = Reflection::getParameterType($parameter);

        if ($type !== null && ! Reflection::isBuiltinType($type)) {
            try {
                $class = $parameter->getClass();
            } catch (ReflectionException $exception) {
                $class = null;
            }

            if ($class === null) {
                if ($parameter->allowsNull()) {
                    return null;
                }

                throw new CompileException(\sprintf(
                    'Class [%s] needed by [%s] not found. Check type hint and \'use\' statements.',
                    $type,
                    $parameter
                ));
            }

            return $class->name;
        }

        // !optional + defaultAvailable = func($a = null, $b) since 5.4.7
        // optional + !defaultAvailable = i.e. Exception::__construct, mysqli::mysqli, ...
        if (($type !== null && $parameter->allowsNull()) || $parameter->isOptional() || $parameter->isDefaultValueAvailable()) {
            return $parameter->isDefaultValueAvailable() ? Reflection::getParameterDefaultValue($parameter) : null;
        }

        throw new CompileException(\sprintf(
            'Parameter [$%s] has no value defined or is not guessable.',
            $parameter->getName()
        ));
    }
}

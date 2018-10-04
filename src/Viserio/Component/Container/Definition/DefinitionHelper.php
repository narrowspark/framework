<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Definition;

use Closure;
use Viserio\Component\Contract\Container\Compiler\Definition as DefinitionContract;
use Viserio\Component\Contract\Container\Exception\InvalidArgumentException;

final class DefinitionHelper
{
    /**
     * Create a Definition on given value.
     *
     * @param string $name
     * @param int    $type
     * @param mixed  $value
     *
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     *
     * @return \Viserio\Component\Contract\Container\Compiler\Definition
     */
    public static function create(string $name, $value, int $type): DefinitionContract
    {
        if ($value instanceof DefinitionContract) {
            throw new InvalidArgumentException('.');
        }

        if (! $value instanceof Closure && (\is_object($value) || is_class($value))) {
            return new ObjectDefinition($name, $value, $type);
        }

        if ($value instanceof Closure) {
            return new ClosureDefinition($name, $value, $type);
        }

        if (is_function($value)) {
            return new FunctionDefinition($name, $value, $type);
        }

        if (is_method($value) || \is_callable($value) || (\is_array($value) && isset($value[1]) && $value[1] === '__invoke')) {
            return new MethodDefinition($name, $value, $type);
        }

        if (\is_array($value)) {
            return new ArrayDefinition($name, $value, $type);
        }

        return new ParameterDefinition($name, $value, $type);
    }
}

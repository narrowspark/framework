<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Compiler;

use Closure;
use Narrowspark\PrettyArray\PrettyArray;
use Viserio\Component\Container\Compiler\Contract\Compiler as CompilerContract;
use Viserio\Component\Container\Compiler\Traits\AnalyzedClosureTrait;

abstract class AbstractCompiler implements CompilerContract
{
    use AnalyzedClosureTrait;

    /**
     * Compile code to php string code.
     *
     * @param mixed $value
     *
     * @throws \ReflectionException
     *
     * @return string
     */
    protected function compileValue($value): string
    {
        if ($value instanceof Closure) {
            return $this->compileClosure($value);
        }

        if (\is_array($value)) {
            return $this->compileArray($value);
        }

        return \var_export($value, true);
    }

    /**
     * Compile a Closure to a php string code.
     *
     * @param \Closure $value
     *
     * @throws \ReflectionException
     *
     * @return string
     */
    protected function compileClosure(Closure $value): string
    {
        return '$this->getFactoryInvoker()->call(static ' . $this->getAnalyzedClosure($value) . ');';
    }

    /**
     * Compile a array to a php string code.
     *
     * @param array $value
     *
     * @return string
     */
    protected function compileArray($value): string
    {
        $array = \array_map(function ($v) {
            return $this->compileValue($v);
        }, $value);

        return PrettyArray::print($array);
    }
}

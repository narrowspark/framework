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
     * @param mixed $value
     *
     * @throws \ReflectionException
     *
     * @return string
     */
    protected function compileValue($value): string
    {
        if ($value instanceof Closure) {
            return '$this->getFactoryInvoker()->call(static ' . $this->getAnalyzedClosure($value) . ');';
        }

        if (\is_array($value)) {
            $array = \array_map(function ($v) {
                return $this->compileValue($v);
            }, $value);

            return PrettyArray::print($array);
        }

        return \var_export($value, true);
    }
}

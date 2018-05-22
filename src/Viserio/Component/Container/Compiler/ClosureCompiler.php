<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Compiler;

use Closure;
use Viserio\Component\Contract\Container\Types as TypesContract;

final class ClosureCompiler extends AbstractCompiler
{
    /**
     * {@inheritdoc}
     */
    public function isSupported(string $id, array $binding): bool
    {
        return $binding[TypesContract::VALUE] instanceof Closure;
    }

    /**
     * {@inheritdoc}
     */
    public function compile(string $id, array $binding): string
    {
        return $this->compileClosure($binding[TypesContract::VALUE]);
    }
}

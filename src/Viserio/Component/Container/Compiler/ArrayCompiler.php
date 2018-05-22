<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Compiler;

use Viserio\Component\Contract\Container\Types as TypesContract;

final class ArrayCompiler extends AbstractCompiler
{
    /**
     * {@inheritdoc}
     */
    public function isSupported(string $id, array $binding): bool
    {
        return \is_array($binding[TypesContract::VALUE]);
    }

    /**
     * {@inheritdoc}
     */
    public function compile(string $id, array $binding): string
    {
        return $this->compileArray($binding[TypesContract::VALUE]);
    }
}

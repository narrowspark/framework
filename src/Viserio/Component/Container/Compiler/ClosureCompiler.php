<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Compiler;

use Viserio\Component\Contract\Container\Types as TypesContract;

final class ClosureCompiler extends AbstractCompiler
{
    /**
     * {@inheritdoc}
     */
    public function isSupported(string $id, array $binding): bool
    {
    }

    /**
     * {@inheritdoc}
     */
    public function compile(string $id, array $binding): string
    {
        $value = $binding[TypesContract::VALUE];
    }
}

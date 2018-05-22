<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Compiler;

use Viserio\Component\Contract\Container\Types as TypesContract;

final class LazyCompiler extends AbstractCompiler
{
    /**
     * {@inheritdoc}
     */
    public function isSupported(string $id, array $binding): bool
    {
        return $binding[TypesContract::BINDING_TYPE] === TypesContract::LAZY;
    }

    /**
     * {@inheritdoc}
     */
    public function compile(string $id, array $binding): string
    {
        $value = $binding[TypesContract::VALUE];

        return
            '        return $this->proxyFactory->createProxy(' . PHP_EOL .
            '            {$class}' . PHP_EOL .
            '            function (&$wrappedObject, $proxy, $method, $params, &$initializer) {' . PHP_EOL .
            '                $wrappedObject = ' . $this->compileValue($value) . ';' . PHP_EOL .
            '                $initializer = null;' . PHP_EOL .
            '                return true;' . PHP_EOL .
            '            }' . PHP_EOL .
            '        );' . PHP_EOL;
    }
}

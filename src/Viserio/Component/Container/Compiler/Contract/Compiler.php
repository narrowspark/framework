<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Compiler\Contract;

/**
 * @internal
 */
interface Compiler
{
    /**
     * Checks if compiler supports the binding.
     *
     * @param string $id
     * @param array  $binding
     *
     * @return bool
     */
    public function isSupported(string $id, array $binding): bool;

    /**
     * @param string $id
     * @param array  $binding
     *
     * @throws \ReflectionException
     *
     * @return string
     */
    public function compile(string $id, array $binding): string;
}

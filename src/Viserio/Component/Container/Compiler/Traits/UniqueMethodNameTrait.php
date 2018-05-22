<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Compiler\Traits;

class UniqueMethodNameTrait
{
    /**
     * Generate a unique method name.
     *
     * @param string $prefix
     *
     * @return string
     */
    protected function generateUniqueMethodName(string $prefix): string
    {
        return \str_replace('.', '', $prefix . \md5(\uniqid((string) \mt_rand(), true)));
    }
}

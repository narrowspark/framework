<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Exception;

interface ExceptionInfo
{
    /**
     * Get the exception information.
     *
     * @param string $id
     * @param int    $code
     *
     * @return array
     */
    public function generate(string $id, int $code): array;
}

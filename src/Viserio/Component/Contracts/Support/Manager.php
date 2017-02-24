<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Support;

interface Manager
{
    /**
     * Get manager config.
     *
     * @return array
     */
    public function getConfig(): array;
}

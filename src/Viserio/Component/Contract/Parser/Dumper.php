<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Parser;

interface Dumper
{
    /**
     * Dumps a array into a string.
     *
     * @param array $data
     *
     * @throws \Viserio\Component\Contract\Parser\Exception\DumpException If dumping fails on some formats
     *
     * @return string
     */
    public function dump(array $data): string;
}

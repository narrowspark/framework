<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Parsers;

interface Dumper
{
    /**
     * Dumps a array into a string.
     *
     * @param array $data
     *
     * @throws \Viserio\Component\Contracts\Parsers\Exception\DumpException If dumping fails on some formats
     *
     * @return string
     */
    public function dump(array $data): string;
}

<?php
declare(strict_types=1);
namespace Viserio\Contracts\Parsers;

interface Dumper
{
    /**
     * Dumps a array into a string.
     *
     * @param array $data
     *
     * @throws \Viserio\Contracts\Parsers\Exception\DumpException If dumping fails on some formats
     *
     * @return string|false
     */
    public function dump(array $data);
}

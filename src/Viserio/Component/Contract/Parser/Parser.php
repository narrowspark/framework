<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Parser;

interface Parser
{
    /**
     * Loads a file and output it content as array.
     *
     * @param string $payload
     *
     * @throws \Viserio\Component\Contract\Parser\Exception\ParseException
     *
     * @return array
     */
    public function parse(string $payload): array;
}

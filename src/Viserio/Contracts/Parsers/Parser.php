<?php
namespace Viserio\Contracts\Parsers;

interface Parser
{
    /**
     * Loads a file and output it content as array.
     *
     * @param string $filename
     *
     * @throws \Viserio\Contracts\Parsers\Exception\LoadingException
     *
     * @return array|string|null
     */
    public function parse($filename);

    /**
     * Checking if file ist supported.
     *
     * @param string $filename
     *
     * @return bool
     */
    public function supports($filename);

    /**
     * Dumps a array into a string.
     *
     * @param array $data
     *
     * @throws \Viserio\Contracts\Parsers\Exception\DumpException If dumping fails
     *
     * @return string|false
     */
    public function dump(array $data);
}

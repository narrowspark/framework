<?php
namespace Viserio\Contracts\Filesystem;

interface Parser
{
    /**
     * Loads a file and output content as array.
     *
     * @param string      $filename
     * @param string|null $group
     *
     * @throws \Viserio\Contracts\Filesystem\Exception\LoadingException
     *
     * @return array|string|null
     */
    public function parse($filename, $group = null);

    /**
     * Checking if file ist supported.
     *
     * @param string $filename
     *
     * @return bool
     */
    public function supports($filename);

    /**
     * Format a data file for saving.
     *
     * @param array $data
     *
     * @return string|false data export
     */
    public function dump(array $data);
}

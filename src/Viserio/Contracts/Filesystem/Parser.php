<?php
namespace Viserio\Contracts\Filesystem;

interface Parser
{
    /**
     * Loads a file and gets its' contents as an array.
     *
     * @param string      $filename
     * @param string|null $group
     *
     * @return array|string|null
     */
    public function load($filename, $group = null);

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
     * @param array $data data
     *
     * @return string|false data export
     */
    public function format(array $data);
}

<?php
namespace Viserio\Contracts\Filesystem;

interface TaggableParser
{
    /**
     * Tag delimiter.
     *
     * @var string
     */
    const TAG_DELIMITER = '::';

    /**
     * Loads a file and output content as array.
     *
     * @param string       $filename
     * @param string|array $group
     *
     * @throws \Viserio\Contracts\Filesystem\Exception\LoadingException
     *
     * @return array|string|null
     */
    public function parse($filename, $taggedKey);

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

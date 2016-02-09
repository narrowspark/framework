<?php
namespace Viserio\Contracts\Filesystem;

interface TaggableParser extends Parser
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
}

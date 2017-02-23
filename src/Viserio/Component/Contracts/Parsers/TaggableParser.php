<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Parsers;

interface TaggableParser extends Parser
{
    /**
     * Tag delimiter.
     *
     * @var string
     */
    public const TAG_DELIMITER = '::';


    /**
     * Set tag key.
     *
     * @param string $key
     *
     * @return $this
     */
    public function setTag(string $key): TaggableParser;
}

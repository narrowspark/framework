<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Parsers;

interface TaggableParser
{
    /**
     * Tag delimiter.
     *
     * @var string
     */
    public const TAG_DELIMITER = '::';
}

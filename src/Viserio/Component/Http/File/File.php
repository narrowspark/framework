<?php
declare(strict_types=1);
namespace Viserio\Component\Http\File;

use SplFileInfo;
use Viserio\Component\Contract\Http\Exception\FileNotFoundException;

class File extends SplFileInfo
{
    /**
     * Constructs a new file from the given path.
     *
     * @param string $path      The path to the file
     * @param bool   $checkPath Whether to check the path or not
     *
     * @throws \Viserio\Component\Contract\Http\Exception\FileNotFoundException If the given path is not a file
     */
    public function __construct(string $path, bool $checkPath = true)
    {
        if ($checkPath && !is_file($path)) {
            throw new FileNotFoundException($path);
        }

        parent::__construct($path);
    }
}

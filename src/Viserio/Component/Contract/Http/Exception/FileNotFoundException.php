<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Http\Exception;

class FileNotFoundException extends RuntimeException
{
    /**
     * Create a new FileNotFoundException instance.
     *
     * @param string $path The path to the file that was not found
     */
    public function __construct($path)
    {
        parent::__construct(\sprintf('The file "%s" does not exist', $path));
    }
}

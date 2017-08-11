<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Http\Exception;

use RuntimeException;

class FileNotFoundException extends RuntimeException implements Exception
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

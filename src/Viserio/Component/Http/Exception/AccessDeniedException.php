<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Exception;

use RuntimeException;

class AccessDeniedException extends RuntimeException
{
    /**
     * Create a new AccessDeniedException instance.
     *
     * @param string $path The path to the accessed file
     */
    public function __construct($path)
    {
        parent::__construct(sprintf('The file %s could not be accessed', $path));
    }
}

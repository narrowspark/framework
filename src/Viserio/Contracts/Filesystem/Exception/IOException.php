<?php
declare(strict_types=1);
namespace Viserio\Contracts\Filesystem\Exception;

use Exception;
use RuntimeException;

class IOException extends RuntimeException
{
    private $path;

    /**
     * Create a new IO exception.
     *
     * @param string $message
     */
    public function __construct(string $message, $code = 0, Exception $previous = null, $path = null)
    {
        $this->path = $path;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the file path.
     *
     * @codeCoverageIgnore
     */
    public function getPath()
    {
        return $this->path;
    }
}

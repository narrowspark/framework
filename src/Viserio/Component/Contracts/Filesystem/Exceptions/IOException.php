<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Filesystem\Exceptions;

use Exception;
use RuntimeException;

class IOException extends RuntimeException
{
    private $path;

    /**
     * Create a new IO exception.
     *
     * @param string         $message
     * @param mixed          $code
     * @param null|Exception $previous
     * @param null|mixed     $path
     */
    public function __construct(string $message, $code = 0, Exception $previous = null, $path = null)
    {
        $this->path = $path;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the file path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}

<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Filesystem\Exception;

use Exception;
use RuntimeException;

class IOException extends RuntimeException
{
    /**
     * @var string|null
     */
    private $path;

    /**
     * Create a new IO exception.
     *
     * @param string         $message
     * @param mixed          $code
     * @param null|Exception $previous
     * @param null|string    $path
     */
    public function __construct(string $message, $code = 0, Exception $previous = null, string $path = null)
    {
        $this->path = $path;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the file path.
     *
     * @return null|string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }
}

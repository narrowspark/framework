<?php
namespace Viserio\Contracts\Filesystem\Exception;

use Exception;
use RuntimeException;

class IOException extends RuntimeException
{
    private $path;

    /**
     * @param string $message
     */
    public function __construct(string $message, $code = 0, Exception $previous = null, $path = null)
    {
        $this->path = $path;

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }
}

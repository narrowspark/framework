<?php
declare(strict_types=1);
namespace Viserio\Foundation\Http\Exceptions;

use Throwable;
use Narrowspark\HttpStatus\Exception\ServiceUnavailableException;

class MaintenanceModeException extends ServiceUnavailableException
{
    /**
     * Create a new MaintenanceModeException instance.
     *
     * @param int         $time
     * @param int|null    $retryAfter
     * @param string|null $message
     * @param \Throwable  $previous
     * @param int         $code
     */
    public function __construct(
        int $time,
        int $retryAfter = null,
        string $message = null,
        Throwable $previous = null,
        int $code = 0
    ) {
        parent::__construct($retryAfter, $message, $previous, $code);
    }
}

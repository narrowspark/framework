<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Http\Exceptions;

use Cake\Chronos\Chronos;
use Narrowspark\HttpStatus\Exception\ServiceUnavailableException;
use Throwable;

class MaintenanceModeException extends ServiceUnavailableException
{
    /**
     * When the application was put in maintenance mode.
     *
     * @var \Cake\Chronos\Chronos
     */
    protected $wentDownAt;

    /**
     * The number of seconds to wait before retrying.
     *
     * @var int
     */
    protected $retryAfter;

    /**
     * When the application should next be available.
     *
     * @var \Cake\Chronos\Chronos
     */
    protected $willBeAvailableAt;

    /**
     * Create a new MaintenanceModeException instance.
     *
     * @param int             $time
     * @param int|null        $retryAfter
     * @param string|null     $message
     * @param \Throwable|null $previous
     */
    public function __construct(
        int $time,
        ?int $retryAfter = null,
        ?string $message = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($retryAfter, $message, $previous);

        $this->wentDownAt = Chronos::createFromTimestamp($time);

        if ($retryAfter) {
            $this->retryAfter = $retryAfter;

            $this->willBeAvailableAt = Chronos::createFromTimestamp($time)->addSeconds($this->retryAfter);
        }
    }
}

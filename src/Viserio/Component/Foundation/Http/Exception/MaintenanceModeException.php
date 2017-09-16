<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Http\Exception;

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
     * @param null|int        $retryAfter
     * @param null|string     $message
     * @param null|\Throwable $previous
     */
    public function __construct(
        int $time,
        ?int $retryAfter = null,
        ?string $message = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $previous, [], 503);

        $this->wentDownAt = Chronos::createFromTimestamp($time);

        if ($retryAfter) {
            $this->retryAfter = $retryAfter;

            $this->willBeAvailableAt = Chronos::createFromTimestamp($time)->addSeconds($this->retryAfter);
        }
    }

    /**
     * Get the time when the application is available.
     *
     * @return \Cake\Chronos\Chronos
     */
    public function getWillBeAvailableAt(): Chronos
    {
        return $this->willBeAvailableAt;
    }

    /**
     * Get time when the application went down.
     *
     * @return \Cake\Chronos\Chronos
     */
    public function getWentDownAt(): Chronos
    {
        return $this->wentDownAt;
    }

    /**
     * Get retry after down.
     *
     * @return int
     */
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}

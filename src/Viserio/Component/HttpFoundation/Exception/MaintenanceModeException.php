<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\HttpFoundation\Exception;

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
     * Get time when the application went down.
     */
    public function getWentDownAt(): Chronos
    {
        return $this->wentDownAt;
    }

    /**
     * Get retry after down.
     */
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }

    /**
     * Get the time when the application is available.
     */
    public function getWillBeAvailableAt(): Chronos
    {
        return $this->willBeAvailableAt;
    }
}

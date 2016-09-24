<?php
declare(strict_types=1);
namespace Viserio\Database\Traits;

use Throwable;
use Viserio\Support\Str;

trait DetectsLostConnections
{
    /**
     * Determine if the given exception was caused by a lost connection.
     *
     * @param \Throwable $exception
     *
     * @return bool
     */
    protected function causedByLostConnection(Throwable $exception): bool
    {
        return Str::containsAny($exception->getMessage(), [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'Error writing data to the connection',
            'Resource deadlock avoided',
        ]);
    }
}

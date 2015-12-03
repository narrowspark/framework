<?php
namespace Viserio\Database\Traits;

use Exception;
use Viserio\Support\Str;

trait DetectsLostConnections
{
    /**
     * Determine if the given exception was caused by a lost connection.
     *
     * @param \Exception $e
     *
     * @return bool
     */
    protected function causedByLostConnection(Exception $e)
    {
        $message = $e->getMessage();

        return Str::contains($message, [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
        ]);
    }
}

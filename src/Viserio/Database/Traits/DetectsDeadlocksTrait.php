<?php
declare(strict_types=1);
namespace Viserio\Connect\Traits;

use Throwable;
use Viserio\Support\Str;

trait DetectsDeadlocks
{
    /**
     * Determine if the given exception was caused by a deadlock.
     *
     * @param \Throwable $exception
     *
     * @return bool
     */
    protected function causedByDeadlock(Throwable $exception): bool
    {
        return Str::containsAny($exception->getMessage(), [
            'Deadlock found when trying to get lock',
            'deadlock detected',
            'The database file is locked',
            'A table in the database is locked',
            'has been chosen as the deadlock victim',
        ]);
    }
}

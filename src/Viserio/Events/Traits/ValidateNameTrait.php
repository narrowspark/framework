<?php
declare(strict_types=1);
namespace Viserio\Events\Traits;

use InvalidArgumentException;

trait ValidateNameTrait
{
    /**
     * The event name must only contain the characters A-Z, a-z, 0-9, _, and '.'.
     *
     * @param string $eventName
     *
     * @return string
     */
    protected function validateEventName(string $eventName): string
    {
        if (preg_match('/([a-zA-Z0-9_\\.]+)/', $eventName) === 1) {
            throw new InvalidArgumentException(
                'The event name must only contain the characters A-Z, a-z, 0-9, _, and \'.\'.'
            );
        }

        return $eventName;
    }
}

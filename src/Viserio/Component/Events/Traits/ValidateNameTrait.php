<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Traits;

use InvalidArgumentException;

trait ValidateNameTrait
{
    /**
     * The event name must only contain the characters A-Z, a-z, 0-9, _, and '.'.
     *
     * @param string $eventName
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    protected function validateEventName(string $eventName): void
    {
        preg_match_all('/([a-zA-Z0-9_\\.]+)/', $eventName, $matches);

        if (count($matches[0]) >= 2) {
            throw new InvalidArgumentException(
                'The event name must only contain the characters A-Z, a-z, 0-9, _, and \'.\'.'
            );
        }
    }
}

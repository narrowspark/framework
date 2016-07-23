<?php

declare(strict_types=1);
namespace Viserio\Exception;

use Ramsey\Uuid\Uuid;
use Throwable;

class ExceptionIdentifier
{
    /**
     * The identification mappings.
     *
     * Note that for performance reasons, we only store up to 16 identifications in
     * memory at a given time.
     *
     * @var string[]
     */
    protected $identification;

    /**
     * Identify the given exception.
     *
     * @param \Throwable $exception
     *
     * @throws \Ramsey\Uuid\Exception\UnsatisfiedDependencyException
     *
     * @return string
     */
    public function identify(Throwable $exception): string
    {
        $hash = spl_object_hash($exception);

        // if we know about the exception, return it's id
        if (isset($this->identification[$hash])) {
            return $this->identification[$hash];
        }

        // cleanup in preparation for the identification
        if (count($this->identification) >= 16) {
            array_shift($this->identification);
        }

        $uuid4 = Uuid::uuid4();

        // generate, store, and return the id
        return $this->identification[$hash] = $uuid4->toString();
    }
}

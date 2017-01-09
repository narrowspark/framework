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

        // generate, store, and return the id
        return $this->identification[$hash] = $this->uuid4(random_bytes(16));
    }

    /**
     * Generate v4 UUID.
     *
     * @param string $data
     *
     * @return string
     */
    private function uuid4(string $data): string
    {
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

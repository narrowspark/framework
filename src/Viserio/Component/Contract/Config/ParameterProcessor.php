<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Config;

interface ParameterProcessor
{
    /**
     * Get the process reference key.
     *
     * @return string
     */
    public static function getReferenceKeyword(): string;

    /**
     * Check if processor supports parameter.
     *
     * @param string $parameter
     *
     * @return bool
     */
    public function supports(string $parameter): bool;

    /**
     * Process parameter through processor.
     *
     * @param string $parameter
     *
     * @return mixed
     */
    public function process(string $parameter);
}

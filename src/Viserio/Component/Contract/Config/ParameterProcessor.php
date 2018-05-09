<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Config;

interface ParameterProcessor
{
    /**
     * @return string
     */
    public function getReferenceKeyword(): string;

    /**
     * @param string $parameter
     *
     * @return bool
     */
    public function supports(string $parameter): bool;

    /**
     * @param string $parameter
     *
     * @return string
     */
    public function process(string $parameter): string;
}

<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Config;

interface ParameterProcessor
{
    /**
     * @return string
     */
    public static function getReferenceKeyword(): string;

    /**
     * @param mixed $parameter
     *
     * @return bool
     */
    public function supports($parameter): bool;

    /**
     * @param string $parameter
     *
     * @return mixed
     */
    public function process(string $parameter);
}

<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container\Compiler;

interface DeprecatedDefinition
{
    /**
     * Whether this definition is deprecated, that means it should not be called
     * anymore.
     *
     * @param bool   $status
     * @param string $template Template message to use if the definition is deprecated
     *
     * @throws \Viserio\Component\Contract\Container\Exception\InvalidArgumentException when the message template is invalid
     *
     * @return void
     */
    public function setDeprecated(bool $status = true, string $template = null): void;

    /**
     * Message to use if this definition is deprecated.
     *
     * @return string
     */
    public function getDeprecationMessage(): string;

    /**
     * Whether this definition is deprecated, that means it should not be called
     * anymore.
     *
     * @return bool
     */
    public function isDeprecated(): bool;
}

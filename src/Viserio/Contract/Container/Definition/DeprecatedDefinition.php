<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Container\Definition;

interface DeprecatedDefinition
{
    /**
     * Whether this definition is deprecated, that means it should not be called
     * anymore.
     *
     * @param string $template Template message to use if the definition is deprecated
     *
     * @throws \Viserio\Contract\Container\Exception\InvalidArgumentException when the message template is invalid
     *
     * @return static
     */
    public function setDeprecated(bool $status = true, ?string $template = null);

    /**
     * Message to use if this definition is deprecated.
     */
    public function getDeprecationMessage(): string;

    /**
     * Whether this definition is deprecated, that means it should not be called
     * anymore.
     */
    public function isDeprecated(): bool;
}

<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\Container\Definition;

interface DeprecatedDefinition
{
    /**
     * Whether this definition is deprecated, that means it should not be called
     * anymore.
     *
     * @param bool   $status
     * @param string $template Template message to use if the definition is deprecated
     *
     * @throws \Viserio\Contract\Container\Exception\InvalidArgumentException when the message template is invalid
     *
     * @return static
     */
    public function setDeprecated(bool $status = true, ?string $template = null);

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

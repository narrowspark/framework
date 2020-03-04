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

interface AliasDefinition extends DeprecatedDefinition
{
    /**
     * Get the definition hash.
     */
    public function getHash(): string;

    /**
     * Get the definition name.
     */
    public function getName(): string;

    /**
     * Set the definition name.
     */
    public function setName(string $original): void;

    /**
     * Check if the alias is public.
     */
    public function isPublic(): bool;

    /**
     * Set the alias name.
     */
    public function setAlias(string $name): void;

    /**
     * Get the alias name.
     */
    public function getAlias(): string;

    /**
     * Set the alias public.
     *
     * @return static
     */
    public function setPublic(bool $bool);
}

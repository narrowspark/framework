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

interface AliasDefinition extends DeprecatedDefinition
{
    /**
     * Get the definition hash.
     *
     * @return string
     */
    public function getHash(): string;

    /**
     * Get the definition name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set the definition name.
     *
     * @param string $original
     *
     * @return void
     */
    public function setName(string $original): void;

    /**
     * Check if the alias is public.
     *
     * @return bool
     */
    public function isPublic(): bool;

    /**
     * Set the alias name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setAlias(string $name): void;

    /**
     * Get the alias name.
     *
     * @return string
     */
    public function getAlias(): string;

    /**
     * Set the alias public.
     *
     * @param bool $bool
     *
     * @return static
     */
    public function setPublic(bool $bool);
}

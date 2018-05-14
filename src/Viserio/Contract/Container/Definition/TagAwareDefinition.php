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

interface TagAwareDefinition
{
    /**
     * Returns all tags.
     *
     * @return array An array of tags
     */
    public function getTags(): array;

    /**
     * Sets tags for this definition.
     *
     * @param array $tags
     *
     * @return static
     */
    public function setTags(array $tags);

    /**
     * Gets a tag by name.
     *
     * @param string $name The tag name
     *
     * @return array An array of attributes
     */
    public function getTag(string $name): array;

    /**
     * Adds a tag for this definition.
     *
     * @param string $name       The tag name
     * @param array  $attributes An array of attributes
     *
     * @return static
     */
    public function addTag(string $name, array $attributes = []);

    /**
     * Whether this definition has a tag with the given name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasTag(string $name): bool;

    /**
     * Clears all tags for a given name.
     *
     * @param string $name The tag name
     *
     * @return static
     */
    public function clearTag(string $name);

    /**
     * Clears the tags for this definition.
     *
     * @return static
     */
    public function clearTags();
}

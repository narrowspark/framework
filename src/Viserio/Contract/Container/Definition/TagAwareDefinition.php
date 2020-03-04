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

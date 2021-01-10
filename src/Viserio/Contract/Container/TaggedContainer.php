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

namespace Viserio\Contract\Container;

use Traversable;

interface TaggedContainer
{
    /**
     * Returns services for a given tag.
     *
     * @param string $tag
     *
     * Example:
     *
     *     $container->bind('foo', \stdClass::class')->addTag('my.tag', ['hello' => 'world']);
     *
     *     foreach ($container->getTagged('my.tag') as $serviceId => $definitionAndTags) {
     *         [$definition, $tags] = $definitionAndTags;
     *
     *         foreach ($tags as $tag) {
     *             echo $tag['hello'];
     *         }
     *     }
     */
    public function getTagged(string $tag): Traversable;

    /**
     * Returns all registered tags.
     *
     * @return string[]
     */
    public function getTags(): array;

    /**
     * Returns all tags not queried by findTaggedServiceIds.
     *
     * @return string[] An array of tags
     */
    public function getUnusedTags(): array;
}

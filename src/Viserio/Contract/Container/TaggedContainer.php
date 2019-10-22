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
     *
     * @return \Traversable
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

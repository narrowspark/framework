<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container;

interface TaggedContainer extends Container
{
    /**
     * Assign a set of bindings to a tag.
     *
     * @param string $tagName
     * @param array  $abstracts
     *
     * @throws \Psr\Container\NotFoundExceptionInterface  no entry was found for **this** identifier
     * @throws \Psr\Container\ContainerExceptionInterface error while retrieving the entry
     *
     * @return void
     */
    public function tag(string $tagName, array $abstracts): void;

    /**
     * Resolve all of the bindings for a given tag.
     *
     * @param string $tag
     *
     * @return array
     */
    public function getTagged(string $tag): array;
}
